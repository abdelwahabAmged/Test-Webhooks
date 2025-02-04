<?php

namespace PWA\FixProductRenderApi\Api;

interface CustomerProductRenderListInterface
{
    /**
     * Collect and retrieve the list of product render info.
     *
     * This info contains raw prices and formatted prices, product name, stock status, store_id, etc.
     *
     * @see \Magento\Catalog\Api\Data\ProductRenderInfoDtoInterface
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int $customerId
     * @param int $storeId
     * @param string $currencyCode
     * @return \Magento\Catalog\Api\Data\ProductRenderSearchResultsInterface
     * @since 102.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $customerId, $storeId, $currencyCode);
}
