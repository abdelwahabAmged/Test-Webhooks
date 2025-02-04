<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Catalog
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2025 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\Catalog\Plugin;

use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\Category as CategoryModel;

class CategoryResourceModelPlugin
{
    /**
     * @param CollectionFactory $categoryCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        protected CollectionFactory $categoryCollectionFactory,
        protected StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Modify the getParentCategories method to include an optional $isActive parameter.
     *
     * @param Category $subject
     * @param callable $proceed
     * @param CategoryModel $category
     * @param bool $isActive
     * @return array
     * @throws LocalizedException
     */
    public function aroundGetParentCategories(
        Category $subject,
        callable $proceed,
        CategoryModel $category,
        $isActive = true
    ) {
        $pathIds = array_reverse(explode(',', (string)$category->getPathInStore()));

        /** @var Collection $categories */
        $categories = $this->categoryCollectionFactory->create();

        $categories->setStore(
            $this->storeManager->getStore()
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'url_key'
        )->addFieldToFilter(
            'entity_id',
            ['in' => $pathIds]
        );

        if ($isActive) {
            $categories->addFieldToFilter(
                'is_active',
                1
            );
        }

        return $categories->load()->getItems();
    }
}
