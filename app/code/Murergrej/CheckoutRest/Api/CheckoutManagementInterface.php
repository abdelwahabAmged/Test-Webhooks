<?php
namespace Murergrej\CheckoutRest\Api;
interface CheckoutManagementInterface {
    /**
     * POST for checkout data
     * @param float $shipping_price_excl_tax
     * @param float $shipping_price_incl_tax
     * @return array
     */
    public function getCheckoutData($shipping_price_excl_tax, $shipping_price_incl_tax);
}
