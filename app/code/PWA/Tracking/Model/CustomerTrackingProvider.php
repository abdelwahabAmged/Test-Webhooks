<?php

namespace PWA\Tracking\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use PWA\Tracking\Api\CustomerTrackingProviderInterface;

class CustomerTrackingProvider implements CustomerTrackingProviderInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var AdminTrackingProvider
     */
    protected $trackingProvider;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        AdminTrackingProvider $trackingProvider
    ) {
        $this->orderRepository = $orderRepository;
        $this->trackingProvider = $trackingProvider;
    }

    public function getTrackingInformation($orderId, $customerId)
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->getCustomerId() != $customerId) {
            throw new NoSuchEntityException();
        }
        return $this->trackingProvider->getTrackingInformation($orderId);
    }
}
