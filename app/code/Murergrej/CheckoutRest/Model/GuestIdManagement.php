<?php
  namespace Murergrej\CheckoutRest\Model;

  use Murergrej\CheckoutRest\Api\GuestIdManagementInterface;

  class GuestIdManagement implements GuestIdManagementInterface {

    /**
     * {@inheritdoc}
     */
    public function getGuestId () {

      try {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quote_mask    = $objectManager->get('\Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface');
        $session       = $objectManager->get('\Magento\Checkout\Model\Session');

        $quote_id_masked = null;
        try {
          $quote_id        = $session->getQuoteId();
          $quote_id_masked = $quote_mask->execute($quote_id);
        } catch (NoSuchEntityException $exception) {}

        $response = [
          'quote_id_masked' => $quote_id_masked
        ];

      } catch(\Exception $e) {
        $response = [ 'error' => $e->getMessage() ];
      }
      return json_encode($response);
    }

  }
