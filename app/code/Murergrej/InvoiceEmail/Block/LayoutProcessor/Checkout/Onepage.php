<?php

namespace Murergrej\InvoiceEmail\Block\LayoutProcessor\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Onepage implements LayoutProcessorInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerInterface
     */
    private $customer;

    public function __construct(Session $customerSession, CustomerRepository $customerRepository)
    {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $customer = $this->getCustomer();
        $invoiceEmailAttribute = $customer ? $customer->getCustomAttribute('invoice_email') : null;
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['before-form']['children']['invoice-email-form']['children']
        ['invoice-email']['value'] = $invoiceEmailAttribute ? $invoiceEmailAttribute->getValue() : '';

        return $jsLayout;
    }

    /**
     * Returns logged customer.
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @return CustomerInterface|null
     */
    protected function getCustomer(): ?CustomerInterface
    {
        if (!$this->customer) {
            if ($this->customerSession->isLoggedIn()) {
                $this->customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            } else {
                return null;
            }
        }
        return $this->customer;
    }
}
