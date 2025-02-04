<?php

namespace Murergrej\PalletShipping\Controller\Adminhtml\System;

class ExportDsv extends ExportAbstract
{
    protected $fileName = 'dsv_shipping.csv';
    protected $blockName = 'Murergrej\PalletShipping\Block\Adminhtml\Carrier\Dsv\Grid';
}
