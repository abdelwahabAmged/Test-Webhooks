<?php

namespace Murergrej\PurchaseOrder\Model\Entity\Attribute\Source;

class CreditLimit extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const VALUE_NU_KUNDE = 0;
    const VALUE_GOD = 1;
    const VALUE_DARLING = 2;

    public function getAllOptions()
    {
        return [
            ['value' => self::VALUE_NU_KUNDE, 'label' => 'Ny kunde'],
            ['value' => self::VALUE_GOD, 'label' => 'God kreditvurdering'],
            ['value' => self::VALUE_DARLING, 'label' => 'Dårlig kreditvurdering'],
        ];
    }

    public function getLabel($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return null;
    }
}
