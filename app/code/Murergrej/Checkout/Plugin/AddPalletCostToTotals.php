<?php

declare(strict_types=1);

/**
 * Addpallet tax to the checkout totals config and update Grand total.
 *
 * @category Murergrej
 * @Package Murergrej_Checkout
 * @author Abanoub Youssef abanoub.youssef@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

namespace Murergrej\Checkout\Plugin;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;

class AddPalletCostToTotals
{
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * AddPalletCostToTotals constructor.
     *
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Add pallet tax to the checkout totals config.
     * update Grand total in cart page
     *
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(DefaultConfigProvider $subject, array $result): array
    {
        try {
            // Get the current quote
            $quote = $this->checkoutSession->getQuote();
            $shippingAddress = $quote->getShippingAddress();
            $palletCost = (float)($quote->getData('pallet_cost') ?? 0);
            $palletTax = $palletCost * 0.25;
            // Add pallet cost the totals data if available
            if ($shippingAddress) {
                $grandTotal = (float)$result['totalsData']['grand_total'];

                $updatedgrandTotal = $grandTotal - $palletTax;
                foreach ($result['totalsData']['total_segments'] as &$segment) {
                    if ($segment['code'] === 'grand_total') {
                        $segment['value'] = $updatedgrandTotal;
                    }
                }
            }
            $result['pallet_tax'] = $palletTax;
        } catch (\Exception $e) {
            // Handle the exception
        }
        return $result;
    }
}
