<?php

namespace PWA\Tracking\Api;

interface CustomerTrackingProviderInterface
{
    /**
     * @param int $orderId
     * @param int $customerId
     * @return mixed
     */
    public function getTrackingInformation($orderId, $customerId);
}
