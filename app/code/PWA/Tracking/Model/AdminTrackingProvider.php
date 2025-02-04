<?php

namespace PWA\Tracking\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use PWA\Tracking\Api\AdminTrackingProviderInterface;

class AdminTrackingProvider implements AdminTrackingProviderInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var TrackingProvider
     */
    protected $trackingProvider;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        TrackingProvider $trackingProvider
    ) {
        $this->orderRepository = $orderRepository;
        $this->trackingProvider = $trackingProvider;
    }

    public function getTrackingInformation($orderId)
    {
        return $this->trackingProvider->getTrackingInformation($this->orderRepository->get($orderId));
    }
}
