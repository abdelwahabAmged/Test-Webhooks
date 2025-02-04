<?php

namespace Murergrej\InvoiceEmail\Api\Data;

interface InvoiceEmailInterface
{
    /**
     * @return string
     */
    public function getInvoiceEmail();

    /**
     * @param string $invoiceEmail
     * @return void
     */
    public function setInvoiceEmail($invoiceEmail);
}
