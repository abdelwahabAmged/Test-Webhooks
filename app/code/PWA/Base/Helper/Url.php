<?php

namespace PWA\Base\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use PWA\Base\Model\Config;

class Url extends AbstractHelper
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Url constructor.
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Context $context, CategoryRepositoryInterface $categoryRepository, StoreManagerInterface $storeManager, Config $config)
    {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * E.g. qwe/asd/zxc.html
     */
    public function getProductPath($product)
    {
        switch ($this->config->getProductUrlMode()) {
            case Config\Source\ProductUrlMode::MODE_MAX_LEVEL:
                return $this->getProductUrlPathByLongestCategory($product);
            case Config\Source\ProductUrlMode::MODE_TOP_CATEGORY:
            default:
                return $this->getProductUrlPathByTopLevelCategory($product);
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     * E.g. qwe/asd.html
     */
    public function getCategoryPath($category)
    {
        return $this->getCategoryUrlPathWithoutEnding($category) . '.html';
    }

    public function getCategoryImageUrl($imagePath)
    {
        return $this->getImageUrl($imagePath, 310, 300, 'resize', true);
    }

    public function getImageUrl($image, $width, $height, $method = 'resize', $full = false)
    {
        $url = $this->concatUrl($this->getApiBaseUrl(), '/img/' . $width . '/' . $height . '/' . $method, $image);
        if ($full) {
            $url .= '?full=1';
        }
        return $url;
    }

    public function concatUrl(...$args)
    {
        $result = null;
        foreach ($args as $part) {
            if (is_null($result)) {
                $result = $part;
            } else {
                $result = $this->_concatUrl($result, $part);
            }
        }
        return $result;
    }

    public function getApiBaseUrl()
    {
        $url = $this->config->getApiBaseUrl();
        if (!$url) {
            $url = $this->config->getBaseUrl();
        }
        return $url;
    }

    public function _concatUrl($base, $path)
    {
        if ($base == '' || $path == '') {
            return $base . $path;
        }
        if (substr($base, -1) == '/') {
            if (substr($path, 0, 1) == '/') {
                return $base . substr($path, 1);
            } else {
                return $base . $path;
            }
        } else if (substr($path, 0, 1) == '/') {
            return $base . $path;
        } else {
            return $base . '/' . $path;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getProductUrlPathByLongestCategory($product)
    {
        $collection = $product->getResource()->getCategoryCollection($product)
            ->addOrder('level', 'DESC')
            ->addOrder('entity_id', 'ASC')
            ->setPageSize(1);
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $collection->getFirstItem();

        $path = $category->getId() ? $this->getCategoryUrlPathWithoutEnding($category) . '/' : '';
        return $path . $this->getUrlKey($product) . '.html';
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getProductUrlPathByTopLevelCategory($product)
    {
        $collection = $product->getResource()->getCategoryCollection($product)
            ->addOrder('level', 'DESC')
            ->addOrder('entity_id', 'ASC')
            ->setPageSize(1);
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $collection->getFirstItem();

        if ($category->getId()) {
            $path = $this->getCategoryUrlPathWithoutEnding($category);
            if ($path != '') {
                $parts = explode('/', $path);
                $path = $parts[0] . '/';
            }
        } else {
            $path = '';
        }
        return $path . $this->getUrlKey($product) . '.html';
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    protected function getCategoryUrlPathWithoutEnding($category)
    {
        $parentCategories = $this->getParentCategories($category);
        usort($parentCategories, function ($a, $b) {
            if ($a->getLevel() == $b->getLevel()) {
                return 0;
            }
            return ($a->getLevel() < $b->getLevel()) ? -1 : 1;
        });

        return implode('/', array_map(function ($_category) use ($category) {
            if (!$_category->hasData('url_key')) {
                $_category = $this->categoryRepository->get($_category->getId(), $_category->hasData('store_id') ? $_category->getStoreId() : null);
            }
            return $_category->getUrlKey();
        }, $parentCategories));
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getUrlKey($product)
    {
        if ($product->hasData('url_key')) {
            return $product->getUrlKey();
        }
        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $product->getResource();
        return $resource->getAttributeRawValue($product->getId(), 'url_key', $this->getStoreId($product));
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    protected function getStoreId($product)
    {
        if ($product->hasData('store_id')) {
            return $product->getStoreId();
        }
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Category[]
     */
    protected function getParentCategories($category)
    {
        $collection = $category->getResourceCollection();
        $collection->addAttributeToFilter('entity_id', ['in' => $category->getPathIds()]);
        $collection->addAttributeToFilter('level', ['gt' => 1]);
        $collection->addAttributeToSelect('url_key');
        $collection->load();

        return $collection->getItems();
    }
}
