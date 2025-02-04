<?php

namespace PWA\Gtm\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use PWA\Gtm\Api\GtmDataLayerInterface;
use PWA\Gtm\Api\CustomerGtmDataLayerInterface;

class CustomerGtmDataLayer implements CustomerGtmDataLayerInterface
{
    /**
     * @var GtmDataLayerInterface
     */
    protected $gtmDataLayer;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    public function __construct(
        GtmDataLayerInterface $gtmDataLayer,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->gtmDataLayer = $gtmDataLayer;
        $this->orderRepository = $orderRepository;
    }

    public function getOrderDataLayer($customerId, $orderId)
    {
        $order = $this->orderRepository->get($orderId);
        if (!$order->getCustomerId() || $order->getCustomerId() != $customerId) {
            throw new NoSuchEntityException(__('No such entity'));
        }
        return $this->gtmDataLayer->getOrderDataLayer($order);
    }
}
