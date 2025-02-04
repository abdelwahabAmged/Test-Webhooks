<?php

namespace Murergrej\PalletShipping\Plugin;

class OrderRepository
{
    /**
     * @param OrderRepository $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterGet(\Magento\Sales\Api\OrderRepositoryInterface $subject, $order)
    {
        $this->processOrder($order);
        return $order;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $result
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function afterGetList(\Magento\Sales\Api\OrderRepositoryInterface $subject, $result)
    {
        foreach ($result->getItems() as $order) {
            $this->processOrder($order);
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function processOrder($order)
    {
        if (($value = $order->getData('pallet_tax')) != '') {
            $order->getExtensionAttributes()->setPalletTax($value);
        }
        if (($value = $order->getData('base_pallet_tax')) != '') {
            $order->getExtensionAttributes()->setBasePalletTax($value);
        }
        if (($value = $order->getData('pallet_tax_incl_tax')) != '') {
            $order->getExtensionAttributes()->setPalletTaxInclTax($value);
        }
        if (($value = $order->getData('base_pallet_tax_incl_tax')) != '') {
            $order->getExtensionAttributes()->setBasePalletTaxInclTax($value);
        }
    }
}
