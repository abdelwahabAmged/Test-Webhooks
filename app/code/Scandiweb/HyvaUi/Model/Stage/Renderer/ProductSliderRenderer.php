<?php
/**
 * @category    Scandiweb
 * @author      Amr Osama <amr.osama@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Model\Stage\Renderer;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\PageBuilder\Model\Stage\RendererInterface;

class ProductSliderRenderer implements RendererInterface
{
    private CategoryCollection $categoryCollection;
    private CategoryListInterface $categoryList;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private ProductCollection $productsFactory;

	/**
	 * @param CategoryCollection $categoryCollection
	 * @param CategoryListInterface $categoryList
	 * @param SearchCriteriaBuilder $searchCriteriaBuilder
	 * @param ProductCollection $productsFactory
	 */
	public function __construct(
        CategoryCollection $categoryCollection,
        CategoryListInterface $categoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductCollection $productsFactory
    ) {
        $this->categoryCollection = $categoryCollection;
        $this->categoryList = $categoryList;
        $this->productsFactory = $productsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

	/**
	 * @param array $params
	 * @return array
	 */
	public function render(array $params): array
    {
        $categories = $this->getCategories($params['categories'], $params['show_category_count']);

        return $categories;
    }

	/**
	 * @param string $categories
	 * @param string $showCategoryCount
	 * @return array
	 */
	private function getCategories(string $categories, string $showCategoryCount): array
    {
            $selectedCategories = explode(',', $categories) ?? [];
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $selectedCategories, 'in')->create();
            $categoryList = $this->categoryList->getList($searchCriteria);
            $data = $categoryList->getItems();
            $returnedData = [];

            foreach ($data as $index => $categoryItem) {
                $returnedData[$index] = [
                    'name' => $categoryItem->getName()
                ];

                if ($showCategoryCount === 'true') {
                    $products = $this->productsFactory->create()->addCategoryFilter($categoryItem);
                    $products->addAttributeToFilter('status', Status::STATUS_ENABLED);

                    $returnedData[$index]['products_count'] = $products->getSize() ?? 0;
                }
            }

            return $returnedData;
    }
}