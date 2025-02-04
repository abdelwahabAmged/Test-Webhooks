<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Murergrej\GroupedProduct\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class InitializeGroupedProductLinks
 */
class InitializeGroupedProductLinks implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * InitializeGroupedProductLinks constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /**
         * Install grouped product link price attribute
         */
        $select = $this->moduleDataSetup->getConnection()
            ->select()
            ->from(
                ['c' => $this->moduleDataSetup->getTable('catalog_product_link_attribute')]
            )
            ->where(
                'c.link_type_id = ?',
                \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
            )
            ->where(
                'c.product_link_attribute_code = ?',
                'link_price'
            );
        $result = $this->moduleDataSetup->getConnection()->fetchAll($select);
        if (!$result) {
            $data = [
                [
                    'link_type_id' => \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED,
                    'product_link_attribute_code' => 'link_price',
                    'data_type' => 'decimal'
                ]
            ];
            $this->moduleDataSetup->getConnection()->insertMultiple(
                $this->moduleDataSetup->getTable('catalog_product_link_attribute'),
                $data
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
