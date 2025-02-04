<?php

namespace PWA\Gtm\Api;

use Magento\Sales\Model\Order;

interface GtmDataLayerInterface
{
    /**
     * @param int $orderId
     * @return mixed
     */
    public function getOrderDataLayer(Order $order);
}
