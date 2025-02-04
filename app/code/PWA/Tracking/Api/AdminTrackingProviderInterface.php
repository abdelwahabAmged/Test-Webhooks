<?php

namespace PWA\Tracking\Api;

interface AdminTrackingProviderInterface
{
    /**
     * @param int $orderId
     * @return mixed
     */
    public function getTrackingInformation($orderId);
}
