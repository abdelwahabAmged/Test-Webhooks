<?php

namespace Murergrej\PalletShipping\Plugin\Model\Cart\TotalsConverter;

class PalletTaxPlugin
{
    /**
     * @param \Magento\Quote\Model\Cart\TotalsConverter $subject
     * @param \Magento\Quote\Api\Data\TotalSegmentInterface[] $result
     * @param \Magento\Quote\Model\Quote\Address\Total[] $addressTotals
     * @return \Magento\Quote\Api\Data\TotalSegmentInterface[]
     */
    public function afterProcess(\Magento\Quote\Model\Cart\TotalsConverter  $subject, $result, $addressTotals)
    {
        foreach ($result as $total) {
            if ($total->getCode() != 'shipping') {
                continue;
            }
            $shippingAddressTotal = null;
            foreach ($addressTotals as $addressTotal) {
                if ($addressTotal->getCode() == 'shipping') {
                    $shippingAddressTotal = $addressTotal;
                    break;
                }
            }
            if ($shippingAddressTotal) {
                $palletTax = $shippingAddressTotal->getAddress()->getPalletTax();
                if ($palletTax) {
                    $newValue = $total->getData('value') + $palletTax;
                    $newValue = number_format($newValue, 4, '.', '');
                    $total->setData('value', $newValue);
                }
            }
            break;
        }
        return $result;
    }
}
