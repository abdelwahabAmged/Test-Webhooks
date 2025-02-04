<?php

namespace Murergrej\Stock\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveBeforeObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getProduct();
        $this->updateStockData($product);
        $this->updateQuantityAndStockStatus($product);
    }

    protected function updateStockData(Product $product)
    {
        $stockData = $product->getData('stock_data');
        if (!isset($stockData) || !is_array($stockData)) {
            return;
        }
        $manageStock = $stockData['manage_stock'] ?? 0;
        if (!$manageStock) {
            return;
        }
        if (!isset($stockData['qty'])) {
            return;
        }
        $isInStock = $stockData['is_in_stock'] ?? 0;
        if (!$isInStock && $stockData['qty'] > 0) {
            $stockData['is_in_stock'] = 1;
            $product->setData('stock_data', $stockData);
        }
    }

    protected function updateQuantityAndStockStatus(Product $product)
    {
        $quantityAndStockStatus = $product->getData('quantity_and_stock_status');
        if (!isset($quantityAndStockStatus) || !is_array($quantityAndStockStatus)) {
            return;
        }
        $isInStock = $quantityAndStockStatus['is_in_stock'] ?? false;
        $qty = $quantityAndStockStatus['qty'] ?? 0;
        if (!$isInStock && $qty > 0) {
            $quantityAndStockStatus['is_in_stock'] = true;
            $product->setData('quantity_and_stock_status', $quantityAndStockStatus);
        }
    }
}
