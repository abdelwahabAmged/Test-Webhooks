<?php

namespace Murergrej\PalletShipping\Block\Adminhtml\Order;

class Totals extends \Magento\Framework\View\Element\Template
{
    const CODE = 'pallet_tax';

    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Order\Totals $parent */
        $parent = $this->getParentBlock();
        $order = $parent->getOrder();
        if($order->getPalletTax()){
            $customAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'pallet_tax',
                    'strong' => false,
                    'value' => $order->getPalletTax(),
                    'label' => $this->getData('label') ?: __('Pallet Tax'),
                ]
            );
            $after = $parent->getTotal('shipping_incl') ? 'shipping_incl' : 'shipping';
            $parent->addTotal($customAmount, $after);
        }
        return $this;
    }
}
