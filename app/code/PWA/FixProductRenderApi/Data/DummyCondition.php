<?php

namespace PWA\FixProductRenderApi\Data;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\CollectionModifierInterface;

class DummyCondition implements CollectionModifierInterface
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    public function apply(AbstractDb $collection)
    {
    }
}
