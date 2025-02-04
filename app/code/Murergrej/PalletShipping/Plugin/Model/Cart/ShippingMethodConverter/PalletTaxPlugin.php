<?php

namespace Murergrej\PalletShipping\Plugin\Model\Cart\ShippingMethodConverter;

class PalletTaxPlugin
{
    /**
     * @param \Magento\Quote\Model\Cart\ShippingMethodConverter $subject
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface $result
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @param string $quoteCurrencyCode
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface
     */
    public function afterModelToDataObject(\Magento\Quote\Model\Cart\ShippingMethodConverter $subject, $result, $rateModel, $quoteCurrencyCode)
    {
        $address = $rateModel->getAddress();
        if (!$address) {
            return $result;
        }
        if ($address->getPalletTax() > 0) {
            $result->setAmount($result->getAmount() + $address->getPalletTax());
            $result->setBaseAmount($result->getBaseAmount() + $address->getBasePalletTax());
            $result->setPriceExclTax($result->getPriceExclTax() + $address->getPalletTax());
            $result->setPriceInclTax($result->getPriceInclTax() + $address->getPalletTaxInclTax());
        }
        return $result;
    }
}
