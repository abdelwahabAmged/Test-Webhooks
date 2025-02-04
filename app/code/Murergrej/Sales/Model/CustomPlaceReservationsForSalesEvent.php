<?php

declare(strict_types=1);

namespace Murergrej\Sales\Model;

use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;

class CustomPlaceReservationsForSalesEvent implements PlaceReservationsForSalesEventInterface
{
    /**
     * @param array $items
     * @param SalesChannelInterface $salesChannel
     * @param SalesEventInterface $salesEvent
     * @return void
     */
    public function execute(
        array $items,
        SalesChannelInterface $salesChannel,
        SalesEventInterface $salesEvent
    ): void {
        // Intentionally do nothing to disable reservation functionality
    }
}
