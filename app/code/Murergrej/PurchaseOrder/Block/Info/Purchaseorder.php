<?php

namespace Murergrej\PurchaseOrder\Block\Info;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\Order;
use Murergrej\PurchaseOrder\Model\Entity\Attribute\Source\CreditLimit;

class Purchaseorder extends \Magento\OfflinePayments\Block\Info\Purchaseorder
{
    /**
     * @var string
     */
    protected $_template = 'Murergrej_PurchaseOrder::info/purchaseorder.phtml';

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository, Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);

        $this->customerRepository = $customerRepository;
    }

    public function isCreditLimit()
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return false;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getInfo()->getOrder();
        if (in_array($order->getState(), $this->getIgnoreStates())) {
            return false;
        }

        $creditLimit = $customer->getCustomAttribute('credit_limit');
        return $creditLimit && $creditLimit->getValue() == CreditLimit::VALUE_DARLING;
    }

    public function getCustomer()
    {
        $customerId = $this->getInfo()->getOrder()->getCustomerId();
        if (!$customerId) {
            return null;
        }

        try {
            return $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    protected function getIgnoreStates()
    {
        return [
            Order::STATE_CLOSED,
            Order::STATE_COMPLETE,
            Order::STATE_CANCELED
        ];
    }
}
