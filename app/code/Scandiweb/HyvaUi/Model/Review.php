<?php

/**
 * @category Scandiweb
 * @package  Scandiweb\HyvaUi
 * @author   Scandiweb <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Model;

use Magento\Review\Model\Review as SourceReviewModel;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as CatalogProductCollectionFactory;
use Magento\Catalog\Model\Product;

class Review extends SourceReviewModel
{
    /**
     * @var CatalogProductCollectionFactory
     */
    protected $catalogProductCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $productFactory,
        \Magento\Review\Model\ResourceModel\Review\Status\CollectionFactory $statusFactory,
        \Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory $summaryFactory,
        \Magento\Review\Model\Review\SummaryFactory $summaryModFactory,
        \Magento\Review\Model\Review\Summary $reviewSummary,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlModel,
        CatalogProductCollectionFactory $catalogProductCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $productFactory,
            $statusFactory,
            $summaryFactory,
            $summaryModFactory,
            $reviewSummary,
            $storeManager,
            $urlModel,
            $resource,
            $resourceCollection,
            $data
        );

        $this->catalogProductCollectionFactory = $catalogProductCollectionFactory;
    }

    public function getProduct()
    {
        $productId = $this->getEntityPkValue();
        $productCollection = $this->catalogProductCollectionFactory->create();

        $productCollection->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', (string) $productId);

        /**
         * @var Product $product
         */
        $product = $productCollection->getFirstItem();

        return $product;
    }
}
