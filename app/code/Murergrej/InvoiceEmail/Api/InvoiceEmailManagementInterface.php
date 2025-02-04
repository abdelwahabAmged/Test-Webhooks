<?php

namespace Murergrej\InvoiceEmail\Api;

interface InvoiceEmailManagementInterface
{
    /**
     * @param int $cartId
     * @param Data\InvoiceEmailInterface $invoiceEmail
     * @return string
     */
    public function saveInvoiceEmail($cartId, Data\InvoiceEmailInterface $invoiceEmail);
}
