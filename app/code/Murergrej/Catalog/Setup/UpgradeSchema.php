<?php
/**
 * UpgradeSchema
 *
 * @category   Murergrej
 * @package    Murergrej_Catalog
 * @author     Abanoub.youssef@scandiweb.com
 */
declare(strict_types=1);

namespace Murergrej\Catalog\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            // Add EAN column to catalog_product_entity_tier_price table
            $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_entity_tier_price'),
                'ean',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'EAN'
                ]
            );
        }

        $setup->endSetup();
    }
}
