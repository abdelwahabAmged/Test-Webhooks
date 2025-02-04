<?php

namespace Murergrej\PalletShipping\Plugin\Model\ShipmentEstimation;

use Magento\Quote\Api\Data\AddressInterface;

class PalletTaxPlugin
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Quote\Api\ShipmentEstimationInterface $subject
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface[] $result
     * @param $cartId
     * @param AddressInterface $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function afterEstimateByExtendedAddress(\Magento\Quote\Api\ShipmentEstimationInterface $subject, $result, $cartId, AddressInterface $address)
    {
        if (empty($result)) {
            return $result;
        }

        $quote = $this->quoteRepository->get($cartId);
        $palletTax = $quote->getPalletTax();
        $basePalletTax = $quote->getBasePalletTax();
        if (!$palletTax) {
            return $result;
        }

        foreach ($result as $item) {
            $item->setAmount($item->getAmount() + $palletTax);
            $item->setBaseAmount($item->getBaseAmount() + $basePalletTax);
        }

        return $result;
    }
}
