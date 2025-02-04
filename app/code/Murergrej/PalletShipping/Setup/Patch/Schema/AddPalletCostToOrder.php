<?php

declare(strict_types=1);

/**
* This schema patch adds a new column 'pallet_cost' to the 'sales_order' table.
* The 'pallet_cost' column stores the cost associated with pallets for an order.
* @category Murergrej
* @package Murergrej_PalletShipping
* @author Abanoub Youssef
* @contact abanoub.youssef@scandiweb.com
* @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

namespace Murergrej\PalletShipping\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;

class AddPalletCostToOrder implements SchemaPatchInterface
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
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // Add 'pallet_cost' column to the 'sales_order' table
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('sales_order'),
            'pallet_cost',
            [
                'type' => Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Pallet Cost',
            ]
        );

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
     */
    public function revert(): void
    {
        $this->moduleDataSetup->getConnection()->dropColumn(
            $this->moduleDataSetup->getTable('sales_order'),
            'pallet_cost'
        );
    }
}
