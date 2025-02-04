<?php
/**
 * @category Murergrej
 * @package Murergrej_Category
 * @author Irmantas Dvareckas info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\Category\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

//Patch sets display value to 'PRODUCTS' for all categories except root categories
class SetCategoriesToProductsOnly implements DataPatchInterface
{
    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        private CategoryCollectionFactory $categoryCollectionFactory
    ) {}

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function apply(): void
    {
        // Fetch all categories except the root categories
        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('display_mode')
            ->addFieldToFilter('level', ['gt' => 1]);

        foreach ($categoryCollection as $category) {
            $category->setData('display_mode', 'PRODUCTS');
            $category->getResource()->saveAttribute($category, 'display_mode');
        }
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
