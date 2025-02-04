<?php

namespace Murergrej\PurchaseOrder\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Murergrej\PurchaseOrder\Model\Entity\Attribute\Source\CreditLimit;

class OrderRepository
{
    protected $customerRepository;

    protected $creditLimitSource;

    public function __construct(CustomerRepositoryInterface $customerRepository, CreditLimit $creditLimitSource)
    {
        $this->customerRepository = $customerRepository;
        $this->creditLimitSource = $creditLimitSource;
    }

    /**
     * @param OrderRepository $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterGet(\Magento\Sales\Api\OrderRepositoryInterface $subject, $order)
    {
        try {
            $customer = $this->customerRepository->getById($order->getCustomerId());
            $creditLimit = $customer->getCustomAttribute('credit_limit');
            if ($creditLimit) {
                $text = $this->creditLimitSource->getLabel($creditLimit->getValue());
            } else {
                $text = $this->creditLimitSource->getLabel(CreditLimit::VALUE_NU_KUNDE);
            }
        } catch (\Exception $e) {
            $text = $this->creditLimitSource->getLabel(CreditLimit::VALUE_NU_KUNDE);
        }
        $order->getExtensionAttributes()->setCreditLimit($text);
        return $order;
    }
}
