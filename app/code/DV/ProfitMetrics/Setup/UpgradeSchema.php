<?php
declare(strict_types=1);

namespace DV\ProfitMetrics\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $installer = $setup;
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            if (!$installer->tableExists('dv_profitmetrics_visitor')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('dv_profitmetrics_visitor')
                )->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Entity Id'
                )->addColumn(
                    'gacid',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Google Analytics Customer ID'
                )->addColumn(
                    'gacid_source',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Source for Google Analytics Customer ID'
                )->addColumn(
                    'gclid',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Google Click ID'
                )->addColumn(
                    'fbp',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Facebook browser ID'
                )->addColumn(
                    'fbc',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Facebook Click ID'
                )->addColumn(
                    'cua',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Customers user agent'
                )->addColumn(
                    'cip',
                    Table::TYPE_TEXT,
                    20,
                    [],
                    'Customers IP'
                )->addColumn(
                    't',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Thrackspec'
                )->addColumn(
                    'timestamp',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Frontend timestamp'
                )->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => Table::TIMESTAMP_INIT
                    ],
                    'Created At'
                )->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => Table::TIMESTAMP_INIT_UPDATE
                    ],
                    'Updated At'
                )->setComment(
                    'ProfitMetrics Visitors'
                );

                $installer->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '0.0.3') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'profitmetrics_visitor_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Profitmetrics Visitor ID',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'profitmetrics_sent_date',
                [
                    'type' => Table::TYPE_DATETIME,
                    'nullable' => true,
                    'comment' => 'Time of order data sending to profitmetrics',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.3') < 0) {
            $installer->getConnection()->modifyColumn(
                $installer->getTable('dv_profitmetrics_visitor'),
                'cip',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 46,
                    'nullable' => true,
                    'comment' => 'Customers IP',
                ]
            );
        }

        $installer->endSetup();
    }
}
