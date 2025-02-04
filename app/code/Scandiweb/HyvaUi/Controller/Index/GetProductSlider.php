<?php

/**
 * @category    Scandiweb
 * @author      Amr Osama <amr.osama@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Scandiweb\HyvaUi\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;

class GetProductSlider extends Action
{
	public const DEFAULT_PRODUCTS_LIMIT = '4';
	protected PageFactory $_pageFactory;
	protected Context $context;
	protected LayoutFactory $layoutFactory;
	protected RawFactory $resultRawFactory;

	/**
	 * @param Context $context
	 * @param PageFactory $pageFactory
	 * @param LayoutFactory $layoutFactory
	 * @param RawFactory $resultRawFactory
	 * @param ResultFactory $resultFactory
	 */
	public function __construct(
		Context $context,
		PageFactory $pageFactory,
		LayoutFactory $layoutFactory,
		RawFactory $resultRawFactory,
		ResultFactory $resultFactory
	) {
		$this->_pageFactory = $pageFactory;
		$this->context = $context;
		$this->layoutFactory = $layoutFactory;
		$this->resultRawFactory = $resultRawFactory;

		$this->resultFactory = $resultFactory;
		parent::__construct($context);
	}

	/**
	 * @return void
	 */
	public function execute()
	{
		$page = $this->_pageFactory->create();

		$categoryId = $this->getRequest()->getParam('category_id');
		$productsLimit = $this->getRequest()->getParam('products_limit') ?? self::DEFAULT_PRODUCTS_LIMIT;

		$response = $page->getLayout()->createBlock(Template::class)
			->setTemplate('Magento_Catalog::product/slider/product-slider.phtml')
			->setData('category_ids', (string)$categoryId)
			->setData('page_size', (string)$productsLimit)
			->setData('hide_details', false)
			->setData('hide_rating_summary', false)
			->setData('sort_attribute', 'price')
			->setData('sort_direction', 'DESC')
			->toHtml();

		$this->getResponse()->setBody($response);

		return $page;
	}
}
