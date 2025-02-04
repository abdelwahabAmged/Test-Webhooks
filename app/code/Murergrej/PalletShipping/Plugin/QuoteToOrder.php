<?php

declare(strict_types=1);

/**
 * This plugin transfers the pallet cost from the quote to the order.
 *
 * @category Murergrej
 * @package Murergrej_PalletShipping
 * @author Abanoub Youssef
 * @contact abanoub.youssef@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

namespace Murergrej\PalletShipping\Plugin;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrder;
use Magento\Sales\Model\Order;

class QuoteToOrder
{
    /**
     * After plugin to transfer pallet_cost from quote to order
     *
     * @param ToOrder $subject
     * @param Order $order
     * @param Address $quote
     * @return Order
     */
    public function afterConvert(
        ToOrder $subject,
        Order $order,
        Address $quote
    ): Order {
        $order->setPalletCost($quote->getPalletCost());
        return $order;
    }
}
