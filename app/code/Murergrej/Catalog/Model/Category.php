<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Catalog
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2025 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Catalog\Model\ResourceModel\Category\TreeFactory;
use Magento\Catalog\Model\Config;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filter\FilterManager;

/**
 * Catalog data helper
 */
class Category extends CategoryModel
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param StoreManagerInterface $storeManager
     * @param CategoryAttributeRepositoryInterface $metadataService
     * @param Tree $categoryTreeResource
     * @param TreeFactory $categoryTreeFactory
     * @param StoreCollectionFactory $storeCollectionFactory
     * @param UrlInterface $url
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Config $catalogConfig
     * @param FilterManager $filter
     * @param State $flatState
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param UrlFinderInterface $urlFinder
     * @param IndexerRegistry $indexerRegistry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StoreManagerInterface $storeManager,
        CategoryAttributeRepositoryInterface $metadataService,
        Tree $categoryTreeResource,
        TreeFactory $categoryTreeFactory,
        StoreCollectionFactory $storeCollectionFactory,
        UrlInterface $url,
        ProductCollectionFactory $productCollectionFactory,
        Config $catalogConfig,
        FilterManager $filter,
        State $flatState,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        UrlFinderInterface $urlFinder,
        IndexerRegistry $indexerRegistry,
        CategoryRepositoryInterface $categoryRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $metadataService,
            $categoryTreeResource,
            $categoryTreeFactory,
            $storeCollectionFactory,
            $url,
            $productCollectionFactory,
            $catalogConfig,
            $filter,
            $flatState,
            $categoryUrlPathGenerator,
            $urlFinder,
            $indexerRegistry,
            $categoryRepository,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param $isActive
     * @return CategoryModel[]|DataObject[]
     */
    public function getParentCategories($isActive = true): array
    {
        return $this->getResource()->getParentCategories($this, $isActive);
    }
}
