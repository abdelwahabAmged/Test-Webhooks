<?php

namespace PWA\Banner\Model;

use PWA\Banner\Api\BannerImageRepositoryInterface;
use PWA\Banner\Model\ResourceModel\BannerImage\CollectionFactory;

class BannerImageRepository implements BannerImageRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function getImages($storeId = null)
    {
        $collection = $this->collectionFactory->create()->addFieldToFilter('status', BannerImage::STATUS_ENABLED);
        if (!is_null($storeId)) {
            $collection->addStoreFilter($storeId);
        }
        return $collection->getItems();
    }
}
