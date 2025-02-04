<?php
/**
 * Observer to handle saving tier price EANs to the database
 *
 * @category   Murergrej
 * @package    Murergrej_Catalog
 * @author     Abanoub.youssef@scandiweb.com
 */
declare(strict_types=1);

namespace Murergrej\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\GroupManagement;

class ProductSaveBefore implements ObserverInterface
{
    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resource;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger,
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        try {
            /** @var Product $product */
            $product = $observer->getProduct();
            $productData = $product->getData('tier_price');
            $entityId = $product->getId();

            if (is_array($productData)) {
                $connection = $this->resource->getConnection();
                $tableName = $this->resource->getTableName('catalog_product_entity_tier_price');

                foreach ($productData as $tierPrice) {
                    if (empty($tierPrice['ean'])) {
                        continue;
                    }

                    $this->processTierPrice($tierPrice, (int)$entityId, $connection, $tableName);
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param array $tierPrice
     * @param int $entityId
     * @param $connection
     * @param string $tableName
     * @return void
     * @throws LocalizedException
     */
    private function processTierPrice(array $tierPrice, int $entityId, $connection, string $tableName): void
    {
        $ean = $tierPrice['ean'];
        $qty = $tierPrice['price_qty'] ?? null;
        $price = isset($tierPrice['price']) ? (float)$tierPrice['price'] : null;
        $percentageValue = isset($tierPrice['percentage_value']) ? (float)$tierPrice['percentage_value'] : null;
        $websiteId = $tierPrice['website_id'] ?? 0;
        $valueType = $tierPrice['value_type'] ?? 'fixed';
        $isAllGroups = (int)$tierPrice['cust_group'] === GroupManagement::CUST_GROUP_ALL;
        $customerGroupId = $isAllGroups ? 0 : ((int)($tierPrice['cust_group'] ?? 1));

        $this->validateTierPrice($qty, $price, $percentageValue, $valueType);

        $price = $valueType === 'percent' ? null : $price;

        if (!empty($tierPrice['price_id'])) {

            $this->updateTierPrice($connection, $tableName, $ean, $entityId, (int)$tierPrice['price_id'], $isAllGroups,
                $price, $percentageValue);
        } else {
            $this->insertOrUpdateTierPrice($connection, $tableName, $ean, $entityId, (int)$customerGroupId, (float)$qty,
                $price,
                $percentageValue !== null ? (float)$percentageValue : null, (int)$websiteId, $isAllGroups);
        }
    }

    /**
     * @param $qty
     * @param $price
     * @param $percentageValue
     * @param string $valueType
     * @return void
     * @throws LocalizedException
     */
    private function validateTierPrice($qty, $price, $percentageValue, string $valueType): void
    {
        if ($qty === null) {
            throw new LocalizedException(__('Tier price quantity is missing.'));
        }

        if (($valueType === 'fixed' && $price === null) || ($valueType === 'percent' && $percentageValue === null)) {
            throw new LocalizedException(
                __($valueType === 'fixed' ? 'Fixed tier price is missing.' : 'Percentage value is missing for tier price.')
            );
        }

        if (!in_array($valueType, ['fixed', 'percent'], true)) {
            throw new LocalizedException(__('Invalid value type for tier price.'));
        }
    }

    /**
     * @param $connection
     * @param string $tableName
     * @param string $ean
     * @param int $entityId
     * @param int $valueId
     * @param bool $isAllGroups
     * @param float|null $price
     * @param float|null $percentageValue
     * @return void
     */
    private function updateTierPrice(
        $connection,
        string $tableName,
        string $ean,
        int $entityId,
        int $valueId,
        bool $isAllGroups,
        ?float $price,
        ?float $percentageValue
    ): void {
        $this->logger->info("Updating Tier Price for value_id: $valueId, EAN: $ean");
        $connection->update(
            $tableName,
            [
                'ean' => $ean,
                'all_groups' => (int)$isAllGroups,
                'value' => $price,
                'percentage_value' => $percentageValue,
            ],
            [
                'entity_id = ?' => $entityId,
                'value_id = ?' => $valueId,
            ]
        );
    }

    /**
     * @param $connection
     * @param string $tableName
     * @param string $ean
     * @param int $entityId
     * @param int $customerGroupId
     * @param float $qty
     * @param float|null $price
     * @param float|null $percentageValue
     * @param int $websiteId
     * @param bool $isAllGroups
     * @return void
     */
    private function insertOrUpdateTierPrice(
        $connection,
        string $tableName,
        string $ean,
        int $entityId,
        int $customerGroupId,
        float $qty,
        ?float $price,
        ?float $percentageValue,
        int $websiteId,
        bool $isAllGroups
    ): void {
        $select = $connection->select()
            ->from($tableName)
            ->where('entity_id = ?', $entityId)
            ->where('qty = ?', $qty)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('website_id = ?', $websiteId);

        $existingTierPrice = $connection->fetchRow($select);

        if ($existingTierPrice) {
            $this->logger->info("Updating existing Tier Price for entity_id: $entityId, EAN: $ean");
            $connection->update(
                $tableName,
                ['ean' => $ean, 'value' => $price, 'percentage_value' => $percentageValue],
                [
                    'entity_id = ?' => $entityId,
                    'qty = ?' => $qty,
                    'customer_group_id = ?' => $customerGroupId,
                    'website_id = ?' => $websiteId,
                ]
            );
        } else {
            $this->logger->info("Inserting new Tier Price for entity_id: $entityId, EAN: $ean");
            $connection->insert($tableName, [
                'entity_id' => $entityId,
                'all_groups' => (int)$isAllGroups,
                'customer_group_id' => $customerGroupId,
                'qty' => $qty,
                'value' => $price,
                'percentage_value' => $percentageValue,
                'website_id' => $websiteId,
                'ean' => $ean,
            ]);
        }
    }
}
