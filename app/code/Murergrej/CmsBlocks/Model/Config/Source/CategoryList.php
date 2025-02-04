<?php
/**
 * @category    Scandiweb
 * @author      Amr Osama <amr.osama@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Murergrej\CmsBlocks\Model\Config\Source;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

class CategoryList implements OptionSourceInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    private CategoryCollectionFactory $categoryCollectionFactory;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(CategoryCollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray(): array
    {
        $categoryList = [];
        $categoryData = $this->toArray();

        foreach ($categoryData as $id => $category) {
            if (!$category['parent_id']) {
                $categoryList[$id]['label'] = $category['name'];
                continue;
            }

            $name = [];
            foreach ($category['path_ids'] as $pathId) {
                $name[] = $categoryData[$pathId]['name'] ?? '';
            }

            $categoryList[$category['parent_id']]['value'][] = [
                'value' => $id,
                'label' => sprintf('%s (ID:%s)', implode(' / ', $name), $id),
            ];
        }

        foreach ($categoryList as $id => $category) {
            if (!isset($category['value'])) {
                unset($categoryList[$id]);
            }
        }

        return $categoryList;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function toArray(): array
    {
        $categoryData = [];
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('entity_id', ['neq' => Category::TREE_ROOT_ID])
            ->setOrder('path');

        /** @var Category $category */
        foreach ($collection->getItems() as $category) {
            $parentId = $category->getParentIds()[1] ?? null;
            $categoryData[$category->getId()] = [
                'name' => $category->getName(),
                'parent_id' => $parentId,
                'path_ids' => array_diff($category->getPathIds(), [Category::TREE_ROOT_ID, $parentId]),
            ];
        }

        return $categoryData;
    }
}
