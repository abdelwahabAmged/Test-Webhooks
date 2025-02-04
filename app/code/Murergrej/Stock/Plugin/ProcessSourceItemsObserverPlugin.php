<?php

namespace Murergrej\Stock\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\Event\Observer;
use Magento\InventoryCatalogAdminUi\Observer\ProcessSourceItemsObserver;

/**
 * Updates is_in_stock in stock data in request params before save product's stock in admin
 * @see \Magento\InventoryCatalogAdminUi\Observer\ProcessSourceItemsObserver
 *
 * Class ProcessSourceItemsObserverPlugin
 * @package Murergrej\Stock\Plugin
 */
class ProcessSourceItemsObserverPlugin
{
    public function beforeExecute(ProcessSourceItemsObserver $subject, Observer $observer)
    {
        /** @var Save $controller */
        $controller = $observer->getEvent()->getController();
        $productData = $controller->getRequest()->getParam('product', []);
        $singleSourceData = $productData['quantity_and_stock_status'] ?? [];
        $assignedSources = $sources['assigned_sources'] ?? [];
        $updated = false;

        $singleSourceData = $this->updateSingleSource($singleSourceData);
        if ($singleSourceData) {
            $productData['quantity_and_stock_status'] = $singleSourceData;
            $updated = true;
        }

        $assignedSources = $this->updateAssignedSources($assignedSources);
        if ($assignedSources) {
            $productData['assigned_sources'] = $assignedSources;
            $updated = true;
        }

        if ($updated) {
            $controller->getRequest()->setParam('product', $productData);
        }
    }

    /**
     * @param array $singleSourceData
     * @return array|null
     */
    protected function updateSingleSource(array $singleSourceData)
    {
        if (empty($singleSourceData)) {
            return null;
        }

        $isInStock = $singleSourceData['is_in_stock'] ?? false;
        $qty = $singleSourceData['qty'] ?? 0;
        if (!$isInStock && $qty > 0) {
            $singleSourceData['is_in_stock'] = true;
            return $singleSourceData;
        }
        return null;
    }

    /**
     * @param array $assignedSources
     * @return |null
     */
    protected function updateAssignedSources(array $assignedSources)
    {
        if (empty($assignedSources)) {
            return null;
        }

        // TODO
        return null;
    }
}
