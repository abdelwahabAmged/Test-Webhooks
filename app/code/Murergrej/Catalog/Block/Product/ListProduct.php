<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Catalog
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Catalog\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\Element;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Block\Product\ListProduct as SourceListProduct;
use Psr\Log\LoggerInterface;
use Murergrej\Catalog\Helper\Data as Catalogdata;
use Murergrej\HelloRetail\Service\HelloRetailService;
use Hyva\Theme\ViewModel\ProductListItem;
use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Product list
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class ListProduct extends SourceListProduct
{
    /**
     * @var int
     */
    public int $totalCount = 0;

    /**
     * @var array
     */
    public array $productSKUAndTrackingCode = [];

    /**
     * @var int
     */
    public int $productsPerPage = 16;

    /**
     * @param LoggerInterface $logger
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Catalogdata $catalogData
     * @param HelloRetailService $helloRetailService
     * @param ProductListItem $productListItemViewModel
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param array $data
     * @param OutputHelper|null $outputHelper
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected ProductCollectionFactory $productCollectionFactory,
        protected Catalogdata $catalogData,
        protected HelloRetailService $helloRetailService,
        protected ProductListItem $productListItemViewModel,
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        array $data = [],
        ?OutputHelper $outputHelper = null
    ) {
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data,
            $outputHelper
        );
    }

    /**
     * @return array|Collection|AbstractCollection|mixed
     */
    public function getLoadedProductCollection(): mixed
    {
        $collection = $this->_getProductCollection();
        $category = $this->getLayer()->getCurrentCategory();

        if ($collection instanceof Collection) {
            return $collection;
        } elseif (isset($collection['productCollection'])) {
            if (str_contains($category->getUrl(), HelloRetailService::POPULAR_PRODUCTS_PAGE_KEY)) {
                $this->totalCount = HelloRetailService::POPULAR_PRODUCTS_COUNT;
            } else {
                $this->totalCount = $collection['totalCount'];
            }

            $this->productSKUAndTrackingCode = $collection['productSKUAndTrackingCode'];

            return [
                'collection' => $collection['productCollection'],
                'sort_options' => $collection['sortOptions'],
                'filters' => $collection['filters']
            ];
        }

        return $collection;
    }

    /**
     * @return Collection|AbstractCollection|array
     */
    protected function _getProductCollection(): Collection|AbstractCollection|array
    {
        $layer = $this->getLayer();
        $category = $layer->getCurrentCategory();
        $pageNumber = (int) $this->getRequest()->getParam('p', 1);
        $isViewAll = $this->getRequest()->getParam('viewAll', '');
        $count = $this->getRequest()->getParam('count', 0);
        $pagesKey = $category->getData('hello_retail_pages_key');

        if (!$category) {
            return [];
        }

        // Handle the case for invalid page number
        if ($pageNumber <= 0) {
            $this->getRequest()->setParams(['p' => 1]);
        }

        $totalProductsToFetch = $pageNumber * $this->productsPerPage;

        $params = [
            'page_key' => $pagesKey,
            'category_url' => $category->getUrl(),
            'is_popular_products_page' =>
                str_contains($category->getUrl(), HelloRetailService::POPULAR_PRODUCTS_PAGE_KEY),
            'hierarchies' => $this->getHierarchiesForCategory(),
            'page_number' => $pageNumber,
            'products_per_page' => $totalProductsToFetch,
            'is_initial_load' => true,
            'view_all' => $isViewAll,
            'total_count' => $count,
            'start' => 0
        ];

        $result = $this->helloRetailService->getProductSKUs($params);

        if (isset($result['products'])) {
            $productNumbers = array_column($result['products'], 'productNumber');
            $this->_productCollection = $this->initializeProductCollection($productNumbers);

            return [
                'productCollection' => $this->_productCollection,
                'totalCount' => isset($result['totalCount']) ? $result['totalCount'] : 0,
                'productSKUAndTrackingCode' => $result['products'],
                'sortOptions' => $result['sortOptions'],
                'filters' => $result['filters']
            ];
        } elseif ($this->_productCollection === null) {
            // Fall back to the default collection
            return $this->initializeProductCollection();
        }

        return $this->_productCollection;
    }

    /**
     * @return mixed|null
     */
    public function getCurrentCategoryId(): mixed
    {
        $layer = $this->getLayer();
        $category = $layer->getCurrentCategory();

        return $category ? $category->getId() : null; // Return the ID or null if there's no current category
    }

    /**
     * @return array
     */
    protected function getHierarchiesForCategory(): array
    {
        $category = $this->getLayer()->getCurrentCategory();
        $hierarchies = $this->catalogData->getBreadcrumbPathFromCategory($category);
        $labels = [];

        foreach ($hierarchies as $category) {
            $labels[] = $category['label'];
        }

        return $labels;
    }

    /**
     * Configures product collection from a layer and returns its instance.
     *
     * Also in the scope of a product collection configuration, this method initiates configuration of Toolbar.
     * The reason to do this is because we have a bunch of legacy code
     * where Toolbar configures several options of a collection and therefore this block depends on the Toolbar.
     *
     * This dependency leads to a situation where Toolbar sometimes called to configure a product collection,
     * and sometimes not.
     *
     * To unify this behavior and prevent potential bugs this dependency is explicitly called
     * when product collection initialized.
     *
     * @return Collection
     */
    private function initializeProductCollection(array $productNumbers = null): Collection
    {
        $layer = $this->getLayer();
        /* @var $layer Layer */
        if ($this->getShowRootCategory()) {
            $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
        }

        // if this is a product view page
        if ($this->_coreRegistry->registry('product')) {
            // get collection of categories this product is associated with
            $categories = $this->_coreRegistry->registry('product')
                ->getCategoryCollection()->setPage(1, 1)
                ->load();
            // if the product is associated with any category
            if ($categories->count()) {
                // show products from this category
                $this->setCategoryId($categories->getIterator()->current()->getId());
            }
        }

        $origCategory = null;
        if ($this->getCategoryId()) {
            try {
                $category = $this->categoryRepository->get($this->getCategoryId());
            } catch (NoSuchEntityException $e) {
                $category = null;
            }

            if ($category) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }

        if ($productNumbers) {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('*')
                ->addAttributeToFilter('sku', ['in' => $productNumbers]);

            // Sort the product collection in the same way that the product numbers are sorted
            // in the response from Hello Retail
            $collection->getSelect()
                ->order(new \Zend_Db_Expr("FIELD(sku, '" . implode("','", $productNumbers) . "')"));
        } else {
            // Fall back to the layer's product collection if productNumbers is empty
            $collection = $layer->getProductCollection();
        }

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities(): array
    {
        $identities = [];
        $category = $this->getLayer()->getCurrentCategory();

        if ($category) {
            $identities[] = [Product::CACHE_PRODUCT_CATEGORY_TAG . '_' . $category->getId()];
        }

        //Check if category page shows only static block (No products)
        if ($category->getData('display_mode') != Category::DM_PAGE) {
            $collection = $this->_getProductCollection();
            $productCollection = $collection instanceof Collection ?
                $collection : ($collection['productCollection'] ?? null);

            foreach ($productCollection as $item) {
                $identities[] = $item->getIdentities();
            }
        }

        $identities = array_merge([], ...$identities);

        return $identities;
    }

    /**
     * Add attribute.
     *
     * @param array|string|integer|Element $code
     * @return $this
     */
    public function addAttribute($code): static
    {
        $collection = $this->_getProductCollection();

        if ($collection instanceof Collection) {
            $collection->addAttributeToSelect($code);
        } elseif (isset($collection['productCollection'])) {
            $collection['productCollection']->addAttributeToSelect($code);
        }

        return $this;
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from another block.
     * (was problem with search result)
     *
     * @return $this
     */
    protected function _beforeToHtml(): static
    {
        $collection = $this->_getProductCollection();
        $productCollection = $collection instanceof Collection ?
            $collection : ($collection['productCollection'] ?? null);

        $this->addToolbarBlock($productCollection);

        if (!$productCollection->isLoaded()) {
            $productCollection->load();
        }

        $categoryId = $this->getLayer()->getCurrentCategory()->getId();

        if ($categoryId) {
            foreach ($productCollection as $product) {
                $product->setData('category_id', $categoryId);
            }
        }

        return $this;
    }

    /**
     * Add toolbar block from product listing layout
     *
     * @param Collection $collection
     */
    private function addToolbarBlock(Collection $collection): void
    {
        $toolbarLayout = $this->getToolbarFromLayout();

        if ($toolbarLayout) {
            $this->configureToolbar($toolbarLayout, $collection);
        }
    }

    /**
     * Get toolbar block from layout
     *
     * @return bool|Toolbar
     */
    private function getToolbarFromLayout(): Toolbar|bool
    {
        $blockName = $this->getToolbarBlockName();
        $toolbarLayout = false;

        if ($blockName) {
            $toolbarLayout = $this->getLayout()->getBlock($blockName);
        }

        return $toolbarLayout;
    }

    /**
     * Configures the Toolbar block with options from this block and configured product collection.
     *
     * The purpose of this method is the one-way sharing of different sorting related data
     * between this block, which is responsible for product list rendering,
     * and the Toolbar block, whose responsibility is a rendering of these options.
     *
     * @param Toolbar $toolbar
     * @param Collection $collection
     * @return void
     */
    private function configureToolbar(Toolbar $toolbar, Collection $collection): void
    {
        // use sortable parameters
        $orders = $this->getAvailableOrders();

        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }

        $sort = $this->getSortBy();

        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }

        $dir = $this->getDefaultDirection();

        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }

        $modes = $this->getModes();

        if ($modes) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }

    /**
     * @param array $productNumbers
     * @return string
     */
    public function getProducItemtHtml(array $productNumbers): string
    {
        $html = '';

        if ($this->getMode() == 'grid') {
            $viewMode         = 'grid';
            $imageDisplayArea = 'category_page_grid';
            $showDescription  = false;
            $templateType     = ReviewRendererInterface::SHORT_VIEW;
        } else {
            $viewMode         = 'list';
            $imageDisplayArea = 'category_page_list';
            $showDescription  = true;
            $templateType     = ReviewRendererInterface::FULL_VIEW;
        }

        if ($productNumbers) {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect('*')
                ->addAttributeToFilter('sku', ['in' => $productNumbers]);

            // Sort the product collection in the same way that the product numbers are sorted
            // in the response from Hello Retail
            $productCollection->getSelect()
                ->order(new \Zend_Db_Expr("FIELD(sku, '" . implode("','", $productNumbers) . "')"));

            foreach ($productCollection as $product) {
                $html .= $this->productListItemViewModel->getItemHtml(
                    $product,
                    $this,
                    $viewMode,
                    $templateType,
                    $imageDisplayArea,
                    $showDescription
                );
            }
        }

        return $html;
    }
}
