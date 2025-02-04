<?php
/**
 * @category    Murergrej
 * @package     Murergrej_HelloRetail
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\HelloRetail\Controller\LoadMore;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Murergrej\Catalog\Helper\Data as Catalogdata;
use Murergrej\HelloRetail\Service\HelloRetailService;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Index extends Action
{
    /**
     * @param Context $context
     * @param HelloRetailService $helloRetailService
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Catalogdata $catalogData
     * @param SerializerInterface $serializerInterface
     */
    public function __construct(
        Context $context,
        protected HelloRetailService $helloRetailService,
        protected CategoryRepositoryInterface $categoryRepository,
        protected Catalogdata $catalogData,
        protected SerializerInterface $serializerInterface
    ) {
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->_view->loadLayout(['default', 'catalog_category_view']);
        $block = $this->_view->getLayout()->getBlock('category.products.list');

        $pageNumber = (int) $this->getRequest()->getParam('p', 1);
        $categoryId = (int) $this->getRequest()->getParam('category_id', 0);
        $viewAll = $this->getRequest()->getParam('viewAll', 0);
        $count = $this->getRequest()->getParam('count', 0);
        $start = $this->getRequest()->getParam('start', 0);
        $productNumbers = null;

        try {
            $category = $this->categoryRepository->get($categoryId); // Load category
            $hierarchies = $this->catalogData->getBreadcrumbPathFromCategory($category);
            $pagesKey = $category->getData('hello_retail_pages_key');
            $labels = [];

            foreach ($hierarchies as $categoryName) {
                $labels[] = $categoryName['label'];
            }

            $params = [
                'page_key' => $pagesKey,
                'category_url' => $category->getUrl(),
                'is_popular_products_page' =>
                    str_contains($category->getUrl(), HelloRetailService::POPULAR_PRODUCTS_PAGE_KEY),
                'hierarchies' => $labels,
                'page_number' => $pageNumber,
                'products_per_page' => $block->productsPerPage,
                'view_all' => $viewAll,
                'total_count' => $count,
                'start' => $start
            ];
        } catch (NoSuchEntityException $e) {
            $this->getResponse()->setStatusHeader(404);

            return;
        }

        if (!empty($params)) {
            $result = $this->helloRetailService->getProductSKUs($params);
            $productNumbers = isset($result['products']) ?
                array_column($result['products'], 'productNumber') : [];
        }

        $response = [
            'success' => true,
            'products' => []
        ];

        if ($productNumbers) {
            $html = $block->getProducItemtHtml($productNumbers);
            $response['products'] = $html;
        }

        $this->getResponse()->representJson($this->serializerInterface->serialize($response));
    }
}
