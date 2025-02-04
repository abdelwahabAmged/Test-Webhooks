<?php

namespace Murergrej\InvoiceEmail\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class AddInvoiceEmailToOrderObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getOrder();
        /** @var $quote Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $invoiceEmail = $this->getInvoiceEmail($quote);
        $order->setData('invoice_email', $invoiceEmail);
    }

    protected function getInvoiceEmail(Quote $quote)
    {
        $invoiceEmail = $quote->getData('invoice_email');
        if (!empty ($invoiceEmail) || $quote->getCustomerIsGuest()) {
            return $invoiceEmail;
        }

        $customer = $quote->getCustomer();
        if (!$customer) {
            return '';
        }
        $attr = $customer->getCustomAttribute('invoice_email');
        return $attr ? $attr->getValue() : '';
    }
}
