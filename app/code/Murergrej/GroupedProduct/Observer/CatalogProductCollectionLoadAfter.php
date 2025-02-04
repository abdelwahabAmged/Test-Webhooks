<?php

namespace Murergrej\GroupedProduct\Observer;

use Magento\Framework\Event\ObserverInterface;

class CatalogProductCollectionLoadAfter implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getCollection();
        if (!$collection instanceof \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection) {
            return;
        }

        if (!$collection->getProduct() || $collection->getProduct()->getTypeId() != \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            return;
        }

        foreach ($collection->getItems() as $product) {
            if ($product->getData('link_price')) {
                $product->setData('price', $product->getData('link_price'));
            }
        }
    }
}
