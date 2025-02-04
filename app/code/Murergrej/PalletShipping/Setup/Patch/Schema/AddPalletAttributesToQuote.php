<?php

declare(strict_types=1);

/**
 * Add Pallet Attributes to Quote Schema Patch
 *
 * This patch adds the 'pallet_count' and 'pallet_cost' columns to the 'quote' and 'sales_order' tables.
 *
 * @category    Murergrej
 * @package     Murergrej_PalletShipping
 * @developer   Abanoub Youssef <info@scandiweb.com>
 */

namespace Murergrej\PalletShipping\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;

class AddPalletAttributesToQuote implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Apply the patch
     *
     * This method adds the 'pallet_count' and 'pallet_cost' columns to the 'quote' and 'sales_order' tables.
     */
    public function apply(): void
    {
        // Start setup
        $this->moduleDataSetup->getConnection()->startSetup();

        // Add 'pallet_count' column to the 'quote' table
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('quote'),
            'pallet_count',
            [
                'type' => Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Pallet Count',
            ]
        );

        // Add 'pallet_cost' column to the 'quote' table
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('quote'),
            'pallet_cost',
            [
                'type' => Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Pallet Cost',
            ]
        );

        // Add 'pallet_count' column to the 'sales_order' table
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('sales_order'),
            'pallet_count',
            [
                'type' => Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Pallet Count',
            ]
        );

        // End setup
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get the dependencies for this patch
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get the aliases for this patch
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Revert changes
     *
     * This method reverts the changes made by the patch by dropping the 'pallet_count' and 'pallet_cost' columns.
     */
    public function revert(): void
    {
        $this->moduleDataSetup->getConnection()->dropColumn(
            $this->moduleDataSetup->getTable('quote'),
            'pallet_count'
        );

        $this->moduleDataSetup->getConnection()->dropColumn(
            $this->moduleDataSetup->getTable('quote'),
            'pallet_cost'
        );

        $this->moduleDataSetup->getConnection()->dropColumn(
            $this->moduleDataSetup->getTable('sales_order'),
            'pallet_count'
        );
    }
}
