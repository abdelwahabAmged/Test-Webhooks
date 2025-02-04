<?php

namespace Murergrej\PalletShipping\Model\ResourceModel\Carrier\DSV;

class Collection extends \Murergrej\PalletShipping\Model\ResourceModel\Carrier\AbstractCarrier\Collection
{
    /**
     * Define resource model and item
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Murergrej\PalletShipping\Model\Carrier\DSV::class,
            \Murergrej\PalletShipping\Model\ResourceModel\Carrier\DSV::class
        );
        parent::_construct();
    }
}
