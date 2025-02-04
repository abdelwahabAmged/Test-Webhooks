<?php

namespace Murergrej\PalletShipping\Block\Adminhtml\Order\Creditmemo;

class Totals extends \Magento\Framework\View\Element\Template
{
    const CODE = 'pallet_tax';

    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Order\Creditmemo\Totals $parent */
        $parent = $this->getParentBlock();
        $creditmemo = $parent->getCreditmemo();
        if($creditmemo->getPalletTax()){
            $customAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'pallet_tax',
                    'strong' => false,
                    'value' => $creditmemo->getPalletTax(),
                    'label' => $this->getData('label') ?: __('Pallet Tax'),
                ]
            );
            $after = $parent->getTotal('shipping_incl') ? 'shipping_incl' : 'shipping';
            $parent->addTotal($customAmount, $after);
        }
        return $this;
    }
}
