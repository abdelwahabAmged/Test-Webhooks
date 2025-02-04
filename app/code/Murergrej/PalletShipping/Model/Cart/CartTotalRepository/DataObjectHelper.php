<?php

namespace Murergrej\PalletShipping\Model\Cart\CartTotalRepository;

class DataObjectHelper extends \Magento\Framework\Api\DataObjectHelper
{
    /**
     * Populate data object using data in array format.
     *
     * @param mixed $dataObject
     * @param array $data
     * @param string $interfaceName
     * @return \Magento\Framework\Api\DataObjectHelper
     */
    public function populateWithArray($dataObject, array $data, $interfaceName)
    {
        if ($interfaceName === \Magento\Quote\Api\Data\TotalsInterface::class
            && $dataObject instanceof \Magento\Quote\Api\Data\TotalsInterface
            && isset($data['pallet_tax'])
            && $data['pallet_tax'] > 0
        ) {
            $palletAdditions = [
                'shipping_amount' => $data['pallet_tax'] ?? 0,
                'base_shipping_amount' => $data['base_pallet_tax'] ?? 0,
                'shipping_tax_amount' => ($data['pallet_tax_incl_tax'] ?? 0) - ($data['pallet_tax'] ?? 0),
                'base_shipping_tax_amount' => ($data['base_pallet_tax_incl_tax'] ?? 0) - ($data['base_pallet_tax'] ?? 0),
                'shipping_incl_tax' => $data['pallet_tax_incl_tax'] ?? 0,
                'base_shipping_incl_tax' => $data['base_pallet_tax_incl_tax'] ?? 0
            ];

            foreach ($palletAdditions as $field => $addition) {
                if (!$addition || !isset($data[$field])) {
                    continue;
                }
                $data[$field] += $addition;
            }
        }
        return parent::populateWithArray($dataObject, $data, $interfaceName);
    }
}
