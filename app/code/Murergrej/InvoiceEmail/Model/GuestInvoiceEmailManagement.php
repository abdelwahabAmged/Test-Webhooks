<?php

namespace Murergrej\InvoiceEmail\Model;

use Murergrej\InvoiceEmail\Api\Data;
use Murergrej\InvoiceEmail\Api\GuestInvoiceEmailManagementInterface;
use Murergrej\InvoiceEmail\Api\InvoiceEmailManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestInvoiceEmailManagement implements GuestInvoiceEmailManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var InvoiceEmailManagementInterface
     */
    private $invoiceEmailManagement;

    /**
     * @param InvoiceEmailManagementInterface $invoiceEmailManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        InvoiceEmailManagementInterface $invoiceEmailManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->invoiceEmailManagement = $invoiceEmailManagement;
    }

    /**
     * @param string $cartId
     * @param Data\InvoiceEmailInterface $invoiceEmail
     * @return string
     */
    public function saveInvoiceEmail($cartId, Data\InvoiceEmailInterface $invoiceEmail)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->invoiceEmailManagement->saveInvoiceEmail($quoteIdMask->getQuoteId(), $invoiceEmail);
    }
}
