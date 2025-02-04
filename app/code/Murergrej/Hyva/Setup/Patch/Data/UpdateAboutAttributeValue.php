<?php
/**
 * @category  Murergrej
 * @package   Murergrej_Hyva
 * @author    Jorgena Shinjatari info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 * @license   https://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Murergrej\Hyva\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ResourceConnection;

class UpdateAboutAttributeValue implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        ResourceConnection $resourceConnection
    )
    {
        $this->setup = $setup;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $connection = $this->setup->getConnection();

        // Fetch all product entity IDs
        $entityIds = $connection->fetchCol("
            SELECT DISTINCT entity_id 
            FROM catalog_product_entity 
            WHERE created_at >= '2025-01-24'
        ");

        foreach ($entityIds as $entityId) {
            $this->insertProductValue($entityId);
        }
    }

    /**
     * @param $entityId
     * @return void
     */
    private function insertProductValue($entityId)
    {
        $connection = $this->setup->getConnection();

        // Get the raw values for the attributes without cleaning
        $valueShortDescription  = $this->getRawValue($entityId, 'short_description');
        $valueLongDescription   = $this->getRawValue($entityId, 'beskrivelse1');

        // Concatenate values and set new value for 'about'
        if (empty($valueShortDescription) && empty($valueLongDescription)) {
            $newValue = '';
        } else {
            $newValue = sprintf('%s%s', $valueShortDescription, $valueLongDescription);
        }

        // Get the attribute ID for 'about'
        $attributeId = $this->getAttributeId('about');

        if (!empty($newValue)) {
            $connection->insert('catalog_product_entity_text', [
                'entity_id'     => $entityId,
                'attribute_id'  => $attributeId,
                'value'         => $newValue,
            ]);
        }
    }

    /**
     * @param $entityId
     * @param $attributeCode
     * @return mixed
     */
    private function getRawValue($entityId, $attributeCode)
    {
        $connection = $this->setup->getConnection();

        // Fetch the value directly without removing outer div tags
        $value = $connection->fetchOne(
            $connection->select()
                ->from('catalog_product_entity_text', 'value')
                ->where('entity_id = ?', $entityId)
                ->where('attribute_id = ?', $this->getAttributeId($attributeCode))
        );

        return $value;
    }

    /**
     * @param $attributeCode
     * @return mixed
     */
    private function getAttributeId($attributeCode)
    {
        return $this->setup->getConnection()->fetchOne(
            $this->setup->getConnection()->select()
                ->from('eav_attribute', 'attribute_id')
                ->where('attribute_code = ?', $attributeCode)
        );
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
}
