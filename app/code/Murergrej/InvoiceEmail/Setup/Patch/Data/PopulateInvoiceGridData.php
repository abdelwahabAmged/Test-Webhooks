<?php

namespace Murergrej\InvoiceEmail\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Ui\Model\ResourceModel\Bookmark\Collection as BookmarkCollection;

class PopulateInvoiceGridData implements DataPatchInterface
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
    protected function populateSalesInvoiceGridColumns()
    {
        // Get connection
        $connection = $this->bookmarkCollection->getConnection();

        // Get grid table names
        $gridTables = [
            $connection->getTableName('sales_invoice_grid')
        ];

        // Loop through grid tables
        foreach ($gridTables as $table) {
            // Build update select
            $select = $connection->select()->join(
                $connection->getTableName('sales_order'),
                sprintf("sales_order.entity_id = %s.order_id", $table),
                ['invoice_email' => 'customer_email']
            )->where($table . '.invoice_email IS NULL');

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
    protected function deleteInvoiceGridBookmarks()
    {
        // Get collection and connection
        $connection = $this->bookmarkCollection->getConnection();

        // Get ID's to delete
        $ids = $this->bookmarkCollection->addFieldToFilter('namespace', [
            'in' => ['sales_order_invoice_grid', 'sales_archive_order_invoice_grid']
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
        $this->populateSalesInvoiceGridColumns();
        $this->deleteInvoiceGridBookmarks();
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
