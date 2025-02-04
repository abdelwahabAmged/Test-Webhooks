<?php

/**
 * @category    Scandiweb
 * @author      Amr Osama <amr.osama@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Block;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;

class ProductSlider extends Template implements BlockInterface
{
	public const DEFAULT_PRODUCTS_LIMIT = '4';

	/**
	 * @var string
	 */
	protected $_template = 'Scandiweb_HyvaUi::scandiweb_ui/product-slider/product-slider.phtml';
	protected CategoryCollection $categoryCollection;
	protected CategoryListInterface $categoryList;
	protected SearchCriteriaBuilder $searchCriteriaBuilder;
	protected ProductCollection $productsFactory;

	/**
	 * @param Context $context
	 * @param CategoryCollection $categoryCollection
	 * @param CategoryListInterface $categoryList
	 * @param SearchCriteriaBuilder $searchCriteriaBuilder
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		CategoryCollection $categoryCollection,
		CategoryListInterface $categoryList,
		SearchCriteriaBuilder $searchCriteriaBuilder,
		ProductCollection $productsFactory,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->categoryCollection = $categoryCollection;
		$this->categoryList = $categoryList;
		$this->searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->productsFactory = $productsFactory;
	}

	/**
	 * @return array
	 */
	public function getCategories(): array
	{
		$selectedCategories = explode(',', $this->getData('categories')) ?? [];

		$searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $selectedCategories, 'in')->create();
		$categoryList = $this->categoryList->getList($searchCriteria);
		$data = $categoryList->getItems();

		if ($this->getData('show_category_count') == 'true') {
			foreach ($data as $categoryItem) {
				$products = $this->productsFactory->create()->addCategoryFilter($categoryItem);
				$products->addAttributeToFilter('status', Status::STATUS_ENABLED);

				$categoryItem['products_count'] = $products->getSize() ?? 0;
			}
		}

		return $data;
	}

	public function getProductSlider()
	{
		$categories = $this->getCategories();

		if ($categories) {
			$categoryId = $categories[0]->getEntityId() ?? 0;
		} else {
			$categoryId = 0;
		}

		$productsLimit = $this->getData('products_limit') ?? self::DEFAULT_PRODUCTS_LIMIT;

		/**
		 * @var Template
		 */
		$block = $this->getLayout()->createBlock(Template::class);
		$html = $block
			->setTemplate('Magento_Catalog::product/slider/product-slider.phtml')
			->setData('category_ids', (string) $categoryId)
			->setData('page_size', (string) $productsLimit)
			->setData('hide_details', false)
			->setData('hide_rating_summary', false)
			->setData('sort_attribute', 'price')
			->setData('sort_direction', 'DESC')
			->toHtml();

		return $html;
	}
}
