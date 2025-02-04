<?php
  namespace Murergrej\CheckoutRest\Model;
  use Murergrej\CheckoutRest\Api\PostManagementInterface;
  class PostManagement implements PostManagementInterface {

    /**
     * {@inheritdoc}
     */
    public function customGetMethod() {

      try {

        $weeeTotals = explode(' ', 'row_amount base_row_amount row_amount_incl_tax base_row_amount_incl_tax');

        $response = [
          'totals' => [
            'fpt' => [
            ]
          ],
          'cart_items' => [
          ]
        ];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $objectManager->get('\Magento\Checkout\Model\Session');
        $quote_repository = $objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');
        $qid = $session->getQuoteId();
        $quote = $quote_repository->get($qid);
        $items = $quote->getAllItems();

        foreach($items as $item) {
          // Save data
          $weeeTax = $item->getWeeeTaxApplied();
          if ($weeeTax != NULL) $weeeTax = json_decode($weeeTax);
          // Fill cart items array
          $response['cart_items'][] = [
            'product_id' => $item->getProductId(),
            'name'       => $item->getName(),
            'sku'        => $item->getSku(),
            'qty'        => $item->getQty(),
            'price'      => $item->getPrice(),
            'fpt'        => $weeeTax
          ];
          // Add up totals
          if ($weeeTax != NULL)
            foreach($weeeTax as $weeeTaxItem) {
              if (!array_key_exists($weeeTaxItem->title, $response['totals']['fpt'])) $a='b';
                $response['totals']['fpt'][$weeeTaxItem->title] =
                  array_reduce($weeeTotals, function ($carry, $item) { $carry[$item] = 0; return $carry; }, []);
              foreach($weeeTotals as $weeeTotalsItem)
                $response['totals']['fpt'][$weeeTaxItem->title][$weeeTotalsItem] += $weeeTaxItem->$weeeTotalsItem;
            }
        }
      } catch(\Exception $e) {
        $response = [
          'error' => $e->getMessage()
        ];
      }
      return json_encode($response);
    }

  }
