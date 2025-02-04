<?php

namespace Murergrej\PalletShipping\Model\Carrier;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Murergrej\PalletShipping\Model\CompositeWeight;
use Murergrej\PalletShipping\Model\CompositeWeightFactory;

abstract class AbstractCarrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const FORCE_NEW_PALLET_WEIGHT = 250;
    const FORCE_IS_SINGLE_PALLET_WEIGHT = 720;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $resultMethodFactory;

    /**
     * @var \Murergrej\PalletShipping\Model\ResourceModel\Carrier\AbstractCarrier
     */
    protected $carrierResource;

    /**
     * @var CompositeWeightFactory
     */
    protected $compositeWeightFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $resultMethodFactory
     * @param \WebShopApps\MatrixRate\Model\ResourceModel\Carrier\MatrixrateFactory $matrixrateFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $resultMethodFactory,
        CompositeWeightFactory $compositeWeightFactory,
        \Murergrej\PalletShipping\Model\ResourceModel\Carrier\AbstractCarrier $carrierResource,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->resultMethodFactory = $resultMethodFactory;
        $this->carrierResource = $carrierResource;
        $this->compositeWeightFactory = $compositeWeightFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

//        if (!$this->hasPalletProducts($request)) {
//            return false;
//        }

        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }

        // Free shipping by qty
        $freeQty = 0;
        if ($request->getAllItems()) {
            $freePackageValue = 0;
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
                            $freeQty += $item->getQty() * ($child->getQty() - $freeShipping);
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeShipping = is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0;
                    $freeQty += $item->getQty() - $freeShipping;
                    $freePackageValue += $item->getBaseRowTotal();
                }
            }
            $oldValue = $request->getPackageValue();
            $request->setPackageValue($oldValue - $freePackageValue);
        }

        $request->setPalletQty($this->calculatePalletQty($request->getAllItems()));

        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();
        $zipRange = $this->getConfigData('zip_range');
        $rateArray = $this->getRate($request, $zipRange);

        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);

        $rate = $this->selectRate($rateArray, $request);
        if ($rate) {
            $method = $this->getRateResultMethod($rate, $request, $freeQty);
            $result->append($method);
        } else {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
            $error = $this->_rateErrorFactory->create(
                [
                    'data' => [
                        'carrier' => $this->_code,
                        'carrier_title' => $this->getConfigData('title'),
                        'error_message' => $this->getConfigData('specificerrmsg'),
                    ],
                ]
            );
            $result->append($error);
        }

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @return float|int|void
     */
    public function calculatePalletQty($items)
    {
        $singlePalletWeight = $this->getPalletWeight();
        $palletOverweight = $this->getAllowedOverweight();
        $maxOverweight = $this->getMaxSummarOverweight();

        $maxPalletWeight = $singlePalletWeight + $palletOverweight;
        $maxOverWeightWithPalletWeight = $singlePalletWeight + $maxOverweight;

        /** @var CompositeWeight[] $pallets */
        $pallets = [];
        /** @var CompositeWeight $additionalWeight */
        $additionalWeight = $this->compositeWeightFactory->create();

        if ($items) {
            foreach ($items as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren()) {
                    $_items = $item->getChildren();
                } else {
                    $_items = [$item];
                }

                /** @var \Magento\Quote\Model\Quote\Item $_item */
                foreach ($_items as $_item) {
                    if ($_item->getParentItem()) {
                        $qty = $_item->getParentItem()->getQty() * $_item->getQty();
                    } else {
                        $qty = $_item->getQty();
                    }
                    switch ($_item->getProduct()->getIsPallet()) {
                        case \Murergrej\PalletShipping\Model\Entity\Attribute\Source\Pallet::VALUE_NO:
                        case \Murergrej\PalletShipping\Model\Entity\Attribute\Source\Pallet::VALUE_PALLET_BY_WEIGHT:
                            for ($i = 0; $i < $qty; $i++) {
                                $additionalWeight[] = $_item->getWeight();
                            }
                            break;
                        case \Murergrej\PalletShipping\Model\Entity\Attribute\Source\Pallet::VALUE_SINGLE_PALLET:
                            for ($i = 0; $i < $qty; $i++) {
                                $pallet = $this->compositeWeightFactory->create();
                                $pallet->setBaseWeight($_item->getWeight());
                                $pallets[] = $pallet;
                            }
                            break;
                    }
                }
            }
        }

        $additionalWeight->rsort();
        $weightThreshold = min($maxOverWeightWithPalletWeight, $maxPalletWeight);
        $weightThreshold = min($weightThreshold, self::FORCE_IS_SINGLE_PALLET_WEIGHT - 0.00001);
        foreach ($additionalWeight->getItems() as $key => $weight) {
            if ($weight <= $weightThreshold) {
                break;
            }

            $pallet = $this->compositeWeightFactory->create();
            $pallet->setBaseWeight($weight);
            $pallets[] = $pallet;
            unset($additionalWeight[$key]);
        }

        $weightThreshold = min(self::FORCE_NEW_PALLET_WEIGHT - 0.00001, $maxOverWeightWithPalletWeight);
        // move weight over max overweight to new pallets
        while ($additionalWeight->getTotal() > $weightThreshold)
        {
            $maxWeight = $additionalWeight->shift();
            /** @var CompositeWeight $pallet */
            $pallet = $this->compositeWeightFactory->create();
            $pallet->setBaseWeight($maxWeight);

            // fill the new pallet to default pallet weight
            if ($pallet->getTotal() < $singlePalletWeight) {
                $pallet->fillFrom($additionalWeight, $singlePalletWeight);
            }

            $pallets[] = $pallet;
        }

        $summarOverweight = 0;
        foreach ($additionalWeight->getItems() as $key => $weight) {
            $emptiestPallet = null;
            foreach ($pallets as $pallet) {
                if (!$emptiestPallet || $emptiestPallet->getTotal() > $pallet->getTotal()) {
                    $emptiestPallet = $pallet;
                }
            }

            $addToExisting = false;
            $newOverweight = 0;
            if ($emptiestPallet && $emptiestPallet->getTotal() + $weight <= $maxPalletWeight) {
                if ($emptiestPallet->getTotal() + $weight > $singlePalletWeight) {
                    if ($emptiestPallet->getTotal() > $singlePalletWeight) {
                        $newOverweight = $weight;
                    } else {
                        $newOverweight = $emptiestPallet->getTotal() + $weight - $singlePalletWeight;
                    }
                    if ($summarOverweight + $newOverweight < $maxOverweight) {
                        $addToExisting = true;
                    }
                } else {
                    $addToExisting = true;
                }
            }
            if ($addToExisting) {
                $emptiestPallet[] = $weight;
                if ($newOverweight) {
                    $summarOverweight += $newOverweight;
                }
            } else {
                $pallet = $this->compositeWeightFactory->create();
                $pallet->setBaseWeight($weight);
                $pallets[] = $pallet;
            }
        }

        $count = count($pallets);
        if ($count != 1) {
            return $count;
        }

        $pallet = $pallets[0];
        if ($pallet->getTotal() >= $singlePalletWeight) {
            return 1;
        } else {
            return $pallet->getTotal() / $singlePalletWeight;
        }
    }

    protected function selectRate($rateArray, RateRequest $request)
    {
        $cheapestRate = null;
        foreach ($rateArray as $rate) {
            if (!$cheapestRate || $cheapestRate['cost'] > $rate['cost']) {
                $cheapestRate = $rate;
            }
        }
        return $cheapestRate;
    }

    protected function getRateResultMethod($rate, RateRequest $request, $freeQty)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->resultMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code . '_' . $rate['pk']);
        $method->setMethodTitle($this->getConfigData('name'));

        if ($request->getFreeShipping() === true || $request->getPackageQty() == $freeQty || $this->isFreeShipping($request)) {
            $shippingPrice = 0;
        } else {
            $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
        }

        $method->setPrice($shippingPrice);
        $method->setCost($rate['cost']);

        return $method;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param bool $zipRange
     * @return array|bool
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request, $zipRange)
    {
        return $this->carrierResource->getRate($request, $zipRange);
    }

    /**
     * @param float $weight
     * @return false|float
     */
    public function getPalletQtyByWeight($weight)
    {
        return ceil($weight / ($this->getPalletWeight() + $this->getAllowedOverweight()));
    }

    protected function hasPalletProducts(RateRequest $request)
    {
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->getData('is_pallet')) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function isFreeShipping(RateRequest $request)
    {
        $freeShippingOrderTotal = $this->getFreeShippingOrderTotal();
        return $freeShippingOrderTotal > 0 && $request->getPackageValue() >= $freeShippingOrderTotal;
    }

    protected function getPalletWeight()
    {
        return $this->getConfigData('pallet_weight');
    }

    protected function getAllowedOverweight()
    {
        return $this->getConfigData('pallet_allowed_overweight');
    }

    protected function getMaxSummarOverweight()
    {
        return $this->getConfigData('summar_allowed_overweight');
    }

    protected function getFreeShippingOrderTotal()
    {
        return $this->getConfigData('free_shipping_order_total');
    }
}
