<?php

namespace Murergrej\OrderGrid\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Ui\Model\ResourceModel\Bookmark\Collection as BookmarkCollection;

class PopulateGridData implements DataPatchInterface
{
    /**
     * @var BookmarkCollection
     */
    private $bookmarkCollection;

    /**
     * @param BookmarkCollection $bookmarkCollection
     */
    public function __construct(
        BookmarkCollection $bookmarkCollection
    ) {
        $this->bookmarkCollection = $bookmarkCollection;
    }

    /**
     * Populate sales order grid tables
     */
    private function populateSalesOrderGridColumns()
    {
        // Get connection
        $connection = $this->bookmarkCollection->getConnection();

        // Get grid table names
        $gridTables = [
            $connection->getTableName('sales_order_grid')
        ];

        // Loop through grid tables
        foreach ($gridTables as $table) {
            // Build update select
            $select = $connection->select()->join(
                $connection->getTableName('sales_order_address'),
                sprintf("sales_order_address.parent_id = %s.entity_id and address_type = 'billing'", $table),
                ['billing_vat_id' => 'vat_id', 'billing_company' => 'company', 'billing_telephone' => 'telephone']
            );

            // Execute SQL query
            $connection->query(
                $connection->updateFromSelect(
                    $select,
                    $table
                )
            );
        }
    }

    /**
     * Refresh UI bookmarks table
     */
    private function deleteOrderGridBookmarks()
    {
        // Get collection and connection
        $connection = $this->bookmarkCollection->getConnection();

        // Get ID's to delete
        $ids = $this->bookmarkCollection->addFieldToFilter('namespace', [
            'in' => ['sales_order_grid', 'sales_archive_order_grid']
        ])->getColumnValues('bookmark_id');

        // Delete
        $connection->delete(
            $this->bookmarkCollection->getMainTable(),
            $connection->quoteInto('bookmark_id IN(?)', $ids)
        );
    }

    /**
     * Delete order grid UI bookmarks
     */
    public function apply()
    {
        $this->populateSalesOrderGridColumns();
        $this->deleteOrderGridBookmarks();
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }
}
