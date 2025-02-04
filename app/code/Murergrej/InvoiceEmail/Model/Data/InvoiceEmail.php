<?php

namespace Murergrej\InvoiceEmail\Model\Data;

use Magento\Framework\DataObject;
use Murergrej\InvoiceEmail\Api\Data\InvoiceEmailInterface;

class InvoiceEmail extends DataObject implements InvoiceEmailInterface
{
    const INVOICE_EMAIL = 'invoice_email';

    public function getInvoiceEmail()
    {
        return $this->getData(self::INVOICE_EMAIL);
    }

    public function setInvoiceEmail($invoiceEmail)
    {
        $this->setData(self::INVOICE_EMAIL, $invoiceEmail);
    }
}
