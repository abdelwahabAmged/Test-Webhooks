<?php

namespace PWA\Product\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class ProductIconLabelAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'icon_top_label_1', [
            'type'     => 'varchar',
            'label'    => 'Top Label 1',
            'input'    => 'media_image',
            'required' => false,
            'sort_order' => 50,
            'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'group' => 'Storefront'
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'icon_top_label_2', [
            'type'     => 'varchar',
            'label'    => 'Top Label 2',
            'input'    => 'media_image',
            'required' => false,
            'sort_order' => 51,
            'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'group' => 'Storefront'
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'icon_bottom_label_1', [
            'type'     => 'varchar',
            'label'    => 'Bottom Label 1',
            'input'    => 'media_image',
            'required' => false,
            'sort_order' => 52,
            'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'group' => 'Storefront'
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'icon_bottom_label_2', [
            'type'     => 'varchar',
            'label'    => 'Bottom Label 2',
            'input'    => 'media_image',
            'required' => false,
            'sort_order' => 53,
            'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'group' => 'Storefront'
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'icon_bottom_label_3', [
            'type'     => 'varchar',
            'label'    => 'Bottom Label 3',
            'input'    => 'media_image',
            'required' => false,
            'sort_order' => 54,
            'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'group' => 'Storefront'
        ]);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
