<?php

namespace Murergrej\PalletShipping\Block\Adminhtml\Order\Invoice;

class Totals extends \Magento\Framework\View\Element\Template
{
    const CODE = 'pallet_tax';

    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Order\Invoice\Totals $parent */
        $parent = $this->getParentBlock();
        $invoice = $parent->getInvoice();
        if($invoice->getPalletTax()){
            $customAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'pallet_tax',
                    'strong' => false,
                    'value' => $invoice->getPalletTax(),
                    'label' => $this->getData('label') ?: __('Pallet Tax')
                ]
            );
            $after = $parent->getTotal('shipping_incl') ? 'shipping_incl' : 'shipping';
            $parent->addTotal($customAmount, $after);
        }
        return $this;
    }
}
