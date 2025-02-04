<?php

namespace Murergrej\PalletShipping\Model\ResourceModel\Carrier;

class HCS extends AbstractCarrier
{
    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('palletshipping_hcs', 'pk');
    }
}
