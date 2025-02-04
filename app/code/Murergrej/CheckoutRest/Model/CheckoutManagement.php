<?php
  namespace Murergrej\CheckoutRest\Model;

  use Murergrej\CheckoutRest\Api\CheckoutManagementInterface;

  class CheckoutManagement implements CheckoutManagementInterface {

    /**
     * {@inheritdoc}
     */
    public function getCheckoutData ($shipping_price_excl_tax, $shipping_price_incl_tax) {

      try {

        $objectManager    = \Magento\Framework\App\ObjectManager::getInstance();
        $session          = $objectManager->get('\Magento\Checkout\Model\Session');
        $quote_repository = $objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');
        $cart             = $objectManager->get('\Magento\Checkout\Model\Cart');
        $qid              = $session->getQuoteId();
        $quote            = $quote_repository->get($qid);
        $items            = $quote->getAllVisibleItems();

        $weeeTotals = explode(' ', 'row_amount base_row_amount row_amount_incl_tax base_row_amount_incl_tax');

        $subtotal_tax      = floatval($quote->getTotals()['tax']->getData()['value']);
        $subtotal_excl_tax = floatval($quote->getSubtotal());
        $subtotal_incl_tax = floatval($subtotal_excl_tax + $subtotal_tax);

        $response = [
          'totals' => [
            'subtotal' => [
              'excl_tax' => $subtotal_excl_tax,
              'incl_tax' => $subtotal_incl_tax,
              'tax'      => $subtotal_tax
            ],
            'fpt' => [],
            'shipping' => [
              'excl_tax' => floatval($shipping_price_excl_tax),
              'incl_tax' => floatval($shipping_price_incl_tax)
            ],
            'tax_base' => round($subtotal_incl_tax / $subtotal_excl_tax - 1, 2)
          ],
          'cart_items' => []
        ];

        foreach($items as $item) {
          // Save data
          $qty     = $item->getQty();
          $weight  = $item->getWeight();
          $weeeTax = $item->getWeeeTaxApplied();
          if ($weeeTax != NULL) $weeeTax = json_decode($weeeTax);
          // Fill cart items array
          $response['cart_items'][] = [
            'product_id' => $item->getProductId(),
            'name'       => $item->getName(),
            'sku'        => $item->getSku(),
            'qty'        => $qty,
            'price'      => $item->getPrice(),
            'weight'     => $weight,
            'fpt'        => $weeeTax
          ];
          // Add up totals
          if ($weeeTax != NULL)
            foreach($weeeTax as $weeeTaxItem) {
              if (!array_key_exists($weeeTaxItem->title, $response['totals']['fpt']))
                $response['totals']['fpt'][$weeeTaxItem->title] =
                  array_reduce($weeeTotals, function ($carry, $item) { $carry[$item] = 0; return $carry; }, []);
              foreach($weeeTotals as $weeeTotalsItem)
                $response['totals']['fpt'][$weeeTaxItem->title][$weeeTotalsItem] += $weeeTaxItem->$weeeTotalsItem;
            }
        }

      } catch(\Exception $e) {
        $response = [ 'error' => $e->getMessage() ];
      }
      return json_encode($response);
    }

  }
