<?php

namespace Murergrej\PalletShipping\Plugin\Model\Quote\Address;

class Rate
{
    public function aroundImportShippingRate(\Magento\Quote\Model\Quote\Address\Rate $subject, callable $proceed, \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate)
    {
        $returnValue = $proceed($rate);

        if ($returnValue) {
            $returnValue->setCost(
                $rate->getCost()
            );
        }

        return $returnValue;
    }
}
