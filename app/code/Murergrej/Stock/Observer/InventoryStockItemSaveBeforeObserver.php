<?php

namespace Murergrej\Stock\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class InventoryStockItemSaveBeforeObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var \Magento\CatalogInventory\Model\Adminhtml\Stock\Item $item */
        $item = $observer->getItem();
        if (!$item->getManageStock()) {
            return;
        }
        if (!$item->getIsInStock() && $item->getQty() > 0) {
            $item->setIsInStock(1);
        }
    }
}
