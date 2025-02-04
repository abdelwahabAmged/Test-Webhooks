<?php
/**
 * @category Murergrej
 * @package Murergrej_Hyva
 * @author Jorgena Shinjatari info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
*/

declare(strict_types=1);

namespace Murergrej\Hyva\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddVisualCodeToEavAttributeOption implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
       ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->endSetup();

        $this->moduleDataSetup->startSetup();

        $tableName = $this->moduleDataSetup->getTable('eav_attribute_option');

        if ($this->moduleDataSetup->getConnection()->isTableExists($tableName)) {
            // Add column to table
            $this->moduleDataSetup->getConnection()->addColumn(
                $tableName,
                'code',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'code'
                ]
            );
        }

        $this->moduleDataSetup->endSetup();
    }
}
