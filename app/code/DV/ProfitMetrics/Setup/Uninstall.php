<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable('dv_profitmetrics_visitor'));
        $installer->getConnection()->dropColumn(
            $installer->getTable('sales_order'),
            'profitmetrics_visitor_id'
        );
        $installer->getConnection()->dropColumn(
            $installer->getTable('sales_order'),
            'profitmetrics_sent_date'
        );

        $installer->endSetup();
    }
}
