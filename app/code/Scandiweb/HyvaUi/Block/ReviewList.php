<?php

/**
 * @category    Scandiweb
 * @author      Scandiweb <info@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;

/**
 * Class Reviews
 */
class ReviewList extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'Scandiweb_HyvaUi::scandiweb_ui/review/review.phtml';

    /**
     * @var CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $reviewCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $reviewCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    /**
     * @return \Magento\Review\Model\ResourceModel\Review\Collection
     */
    public function getAllReviews()
    {
        $items_limit = (int) $this->getData('items_limit');
        $reviewsCollection = $this->reviewCollectionFactory->create()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setPageSize($items_limit)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addRateVotes()
            ->setDateOrder();

        return $reviewsCollection;
    }

    public function getBaseMediaURL()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getProductFullImageURL($product)
    {
        $baseMediaUrl = $this->getBaseMediaURL();
        $productImageUrl = $product->getImage();
        $fullImageUrl = $baseMediaUrl . 'catalog/product' . $productImageUrl;

        return $fullImageUrl;
    }
}
