<?php

namespace Murergrej\PalletShipping\Block\Adminhtml\Order\Shipping;

use Magento\Sales\Model\Order;

class Cost extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve available order
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return Order
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if ($this->_coreRegistry->registry('current_order')) {
            return $this->_coreRegistry->registry('current_order');
        }
        if ($this->_coreRegistry->registry('order')) {
            return $this->_coreRegistry->registry('order');
        }
        if ($this->getInvoice()) {
            return $this->getInvoice()->getOrder();
        }
        if ($this->getCreditmemo()) {
            return $this->getCreditmemo()->getOrder();
        }
        if ($this->getItem()->getOrder()) {
            return $this->getItem()->getOrder();
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the order instance right now.'));
    }

    public function shouldShow()
    {
        return in_array($this->getOrder()->getShippingMethod(true)->getCarrierCode(), ['hcs', 'dsv']);
    }

    public function getShippingCost()
    {
        return $this->getOrder()->getShippingCost();
    }

    public function getFormattedShippingCost()
    {
        return $this->getOrder()->formatBasePrice($this->getOrder()->getShippingCost());
    }
}
