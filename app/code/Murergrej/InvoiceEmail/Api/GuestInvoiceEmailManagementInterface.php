<?php

namespace Murergrej\InvoiceEmail\Api;

interface GuestInvoiceEmailManagementInterface
{
    /**
     * @param string $cartId
     * @param Data\InvoiceEmailInterface $invoiceEmail
     * @return string
     */
    public function saveInvoiceEmail($cartId, Data\InvoiceEmailInterface $invoiceEmail);
}
