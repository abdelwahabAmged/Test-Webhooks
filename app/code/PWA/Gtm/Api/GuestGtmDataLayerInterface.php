<?php

namespace PWA\Gtm\Api;

interface GuestGtmDataLayerInterface
{
    /**
     * @param string $cartId
     * @param int $orderId
     * @return mixed
     */
    public function getOrderDataLayer($cartId, $orderId);
}
