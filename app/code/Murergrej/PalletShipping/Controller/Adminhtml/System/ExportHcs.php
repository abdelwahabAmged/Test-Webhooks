<?php

namespace Murergrej\PalletShipping\Controller\Adminhtml\System;

class ExportHcs extends ExportAbstract
{
    protected $fileName = 'hcs_shipping.csv';
    protected $blockName = 'Murergrej\PalletShipping\Block\Adminhtml\Carrier\Hcs\Grid';
}
