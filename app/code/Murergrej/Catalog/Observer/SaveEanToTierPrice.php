<?php
/**
 * Observer to handle saving tier price EANs to the database
 *
 * @category   Murergrej
 * @package    Murergrej_Catalog
 * @author     Abanoub.youssef@scandiweb.com
 *
 */
declare(strict_types=1);
namespace Murergrej\Catalog\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ResourceConnection;

class SaveEanToTierPrice implements ObserverInterface
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * SaveEanToTierPrice constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Save EAN to tier price
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        /** @var Product $product */
        $product = $observer->getProduct(); // Get the product from the observer

        // Get all data passed from the Admin Panel
        $productData = $product->getData();
        $entityId = $product->getId();

        if (isset($productData['tier_price']) && is_array($productData['tier_price'])) {

            // Access the database connection
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('catalog_product_entity_tier_price');
            // Loop through the tier prices
            foreach ($productData['tier_price'] as $tierPrice) {
                if (isset($tierPrice['ean'], $tierPrice['price_id'])) {
                    // Get the EAN and value_id (primary key of the tier price row)
                    $ean = $tierPrice['ean'];
                    $valueId = $tierPrice['price_id'];
                    // Update the specific tier price row in the catalog_product_entity_tier_price table
                    $connection->update(
                        $tableName,
                        ['ean' => $ean], // Update the EAN column
                        [
                            'entity_id = ?' => $entityId, // Product entity ID
                            'value_id = ?' => $valueId // Specific tier price row ID
                        ]
                    );
                }
            }
        }
    }
}
