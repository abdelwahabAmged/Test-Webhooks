<?php

namespace PWA\Gtm\Api;

interface CustomerGtmDataLayerInterface
{
    /**
     * @param int $customerId
     * @param int $orderId
     * @return mixed
     */
    public function getOrderDataLayer($customerId, $orderId);
}
