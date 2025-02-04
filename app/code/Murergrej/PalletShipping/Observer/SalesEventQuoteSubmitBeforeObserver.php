<?php
/**
 * Sales Event Quote Submit Before Observer
 *
 * This observer is responsible for transferring pallet-related data from the quote to the order
 * before the order is submitted.
 *
 * @category    Murergrej
 * @package     Murergrej_PalletShipping
 * @developer   Abanoub Youssef <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Murergrej\PalletShipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SalesEventQuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * Transfer pallet-related data from quote to order
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        // Transfer pallet-related data
        $order->setShippingCost($quote->getShippingAddress()->getShippingCost());
        $order->setPalletTax($quote->getShippingAddress()->getPalletTax());
        $order->setBasePalletTax($quote->getShippingAddress()->getBasePalletTax());
        $order->setPalletTaxInclTax($quote->getShippingAddress()->getPalletTaxInclTax());
        $order->setBasePalletTaxInclTax($quote->getShippingAddress()->getBasePalletTaxInclTax());

        // Add pallet_count transfer
        $order->setPalletCount($quote->getData('pallet_count'));
        $order->setPalletCost($quote->getData('pallet_cost'));

        return $this;
    }
}
