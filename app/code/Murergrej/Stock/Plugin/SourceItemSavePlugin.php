<?php

namespace Murergrej\Stock\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Inventory\Model\SourceItem\Command\SourceItemsSave;
use Magento\InventoryCatalog\Model\SourceItemsSaveSynchronization\SetDataToLegacyCatalogInventory;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Psr\Log\LoggerInterface;

/**
 * Sends email if actual stock qty became zero (do not count reservations, e. g. after shipment was created but not after order was created)
 *
 * Class SourceItemSavePlugin
 * @package Murergrej\Stock\Plugin
 */
class SourceItemSavePlugin
{
    /**
     * @var DefaultSourceProviderInterface
     */
    protected $defaultSourceProvider;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Murergrej\Stock\Helper\Data
     */
    protected $data;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param GetProductTypesBySkusInterface $getProductTypeBySku
     * @param SetDataToLegacyCatalogInventory $setDataToLegacyCatalogInventory
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        ProductRepositoryInterface $productRepository,
        \Murergrej\Stock\Helper\Data $data,
        LoggerInterface $logger
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->productRepository = $productRepository;
        $this->data = $data;
        $this->logger = $logger;
    }

    /**
     * @param SourceItemsSave $subject
     * @param $result
     * @param \Magento\Inventory\Model\SourceItem[] $sourceItems
     */
    public function afterExecute(SourceItemsSave $subject, $result, array $sourceItems)
    {
        $this->processNotification($sourceItems);
        return $result;
    }

    protected function processNotification(array $sourceItems)
    {
        if (!$this->data->isEnabled()) {
            return;
        }

        $qty = $this->data->getQty();
        $lowStockItems = [];
        foreach ($sourceItems as $sourceItem) {
            // TODO: support multy stock
            if ($sourceItem->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                continue;
            }
            if ($sourceItem->getQuantity() > $qty) {
                continue;
            }

            try {
                $product = $this->productRepository->get($sourceItem->getSku());
                $lowStockItems[] = [
                    'name' => $product->getName(),
                    'sku' => $sourceItem->getSku(),
                    'status' => $sourceItem->getStatus(),
                    'quantity' => $sourceItem->getQuantity()
                ];
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }

        if (empty($lowStockItems)) {
            return;
        }

        try {
            $this->data->notify($lowStockItems);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
