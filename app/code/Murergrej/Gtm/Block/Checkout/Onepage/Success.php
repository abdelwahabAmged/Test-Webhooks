<?php

namespace Murergrej\Gtm\Block\Checkout\Onepage;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    const XML_PATH_GTM_TRANSACTION_ENABLED = 'murergrej_gtm/data_layer/enable_transactions';

    /**
     * @return bool
     */
    public function isGtmTransactionEnabled()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_GTM_TRANSACTION_ENABLED);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * @return false|string
     */
    public function getTransactionEventJson()
    {
        return json_encode($this->getTransactionEvent());
    }

    public function getTransactionEvent()
    {
        $order = $this->getOrder();
        return [
            'event' => 'transaction',
            'ecommerce' => [
                'purchase' => [
                    'actionField' => [
                        'id' => (string)$order->getIncrementId(),
                        'affiliation' => 'Online Store',
                        'revenue' => (string)$order->getGrandTotal(),
                        'tax' => (string)$order->getTaxAmount(),
                        'shipping' => (string)$order->getShippingAmount(),
                        'coupon' => (string)$order->getCouponCode()
                    ],
                    'products' => $this->getProductsData($order)
                ]
            ]
        ];
    }

    protected function getProductsData(\Magento\Sales\Model\Order $order)
    {

        $data = [];
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $data[] = [
                'name' => (string)$item->getName(),
                'id' => (string)$item->getProductId(),
                'price' => (string)$item->getRowTotalInclTax(),
                'brand' => 'Murergrej.dk',
                'category' => $this->getItemCategoryName($item),
                'variant' => '',
                'quantity' => (int)$item->getQtyOrdered(),
                'coupon' => ''
            ];
        }
        return $data;
    }

    protected function getItemCategoryName(\Magento\Sales\Model\Order\Item $item)
    {
        $category = null;
        foreach ($item->getProduct()->getCategoryCollection() as $_category) {
            if (!$category || $category->getLevel() < $_category->getLevel()) {
                $category = $_category;
            }
        }
        return $category ? $category->getName() : '';
    }
}
