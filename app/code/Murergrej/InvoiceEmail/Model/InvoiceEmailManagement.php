<?php

namespace Murergrej\InvoiceEmail\Model;

use Murergrej\InvoiceEmail\Api\Data;
use Murergrej\InvoiceEmail\Api\InvoiceEmailManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class InvoiceEmailManagement implements InvoiceEmailManagementInterface
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    public function __construct(CartRepositoryInterface $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param $cartId
     * @param Data\InvoiceEmailInterface $invoiceEmail
     * @return string
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function saveInvoiceEmail($cartId, Data\InvoiceEmailInterface $invoiceEmail)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        try {
            $quote->setData('invoice_email', $invoiceEmail->getInvoiceEmail());
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('The invoice email could not be saved'));
        }

        return $invoiceEmail->getInvoiceEmail();
    }
}
