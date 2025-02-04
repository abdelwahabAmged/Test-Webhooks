<?php

namespace Murergrej\MatrixRate\Block\Adminhtml\Carrier\Matrixrate;

class Grid extends \WebShopApps\MatrixRate\Block\Adminhtml\Carrier\Matrixrate\Grid
{
    protected function _prepareColumns()
    {
        $this->addColumnAfter('order_total', [
            'header' => __('Order Total'),
            'index' => 'order_total'
        ], 'shipping_method');
        return parent::_prepareColumns();
    }
}
