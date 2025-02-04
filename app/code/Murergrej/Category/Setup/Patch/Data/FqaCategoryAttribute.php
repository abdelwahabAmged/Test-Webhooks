<?php
/**
 * @category Murergrej
 * @package Murergrej_Category
 * @author Irmantas Dvareckas info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

namespace Murergrej\Category\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Validator\ValidateException;

class FqaCategoryAttribute implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        protected EavSetupFactory $eavSetupFactory
    ) {}

    /**
     * @return void
     * @throws LocalizedException
     * @throws ValidateException
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(Category::ENTITY, 'fqa_category', [
            'type'     => 'text',
            'label'    => 'FAQ',
            'input'    => 'textarea',
            'required' => false,
            'sort_order' => 4,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'wysiwyg_enabled' => true,
            'is_html_allowed_on_front' => true,
            'group' => 'General Information',
        ]);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
