<?php

namespace Murergrej\PalletShipping\Plugin\Model\Quote\Address;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;

class RateCollector
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function afterCollectRates(\Magento\Quote\Model\Quote\Address\RateCollectorInterface $subject, $result, RateRequest $request)
    {
        if (!$subject instanceof \Magento\Shipping\Model\Shipping) {
            return $result;
        }

        $rateResult = $subject->getResult();
        $rates = $rateResult->getAllRates();

        if (!($carriers = $this->getPalleteCarriers($rates))) {
            return $result;
        }

        $removeFromWeight = $this->getMinimalRemoveWeight($carriers);
        if (!$removeFromWeight) {
            return $result;
        }

        if ($request->getPackageWeight() >= $removeFromWeight) {
            $preserveCarriers = ['dsv', 'hcs', 'freeshipping'];
            $changed = false;
            foreach ($rates as $key => $rate) {
                if (!in_array($rate->getCarrier(), $preserveCarriers)) {
                    unset($rates[$key]);
                    $changed = true;
                }
            }

            if ($changed) {
                $rateResult->reset();
                foreach ($rates as $rate) {
                    $rateResult->append($rate);
                }
            }
        }

        return $result;
    }

    protected function getPalleteCarriers($rates)
    {
        $palletCarriers = ['dsv', 'hcs'];
        $result = [];
        foreach ($rates as $rate) {
            if (in_array($rate->getCarrier(), $palletCarriers)) {
                $result[] = $rate->getCarrier();
            }
        }
        return $result;
    }

    protected function getMinimalRemoveWeight($carriers)
    {
        $minimal = false;
        foreach ($carriers as $carrier) {
            $weight = $this->scopeConfig->getValue('carriers/' . $carrier . '/remove_shipping_methods_weight');
            if ($minimal === false || $weight <  $minimal) {
                $minimal = $weight;
            }
        }
        return $minimal;
    }
}
