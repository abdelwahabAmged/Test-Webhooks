<?php

namespace Murergrej\InvoiceEmail\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;

class InvoiceSender extends \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
{
    protected function prepareTemplate(Order $order)
    {
        parent::prepareTemplate($order);
        if (($invoiceEmail = $order->getData('invoice_email')) != '') {
            $this->identityContainer->setCustomerEmail($invoiceEmail);
        }
    }
}
