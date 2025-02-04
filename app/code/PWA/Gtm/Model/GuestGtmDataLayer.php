<?php

namespace PWA\Gtm\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use PWA\Gtm\Api\GtmDataLayerInterface;
use PWA\Gtm\Api\GuestGtmDataLayerInterface;

class GuestGtmDataLayer implements GuestGtmDataLayerInterface
{
    /**
     * @var GtmDataLayerInterface
     */
    protected $gtmDataLayer;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    public function __construct(
        GtmDataLayerInterface $gtmDataLayer,
        OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->gtmDataLayer = $gtmDataLayer;
        $this->orderRepository = $orderRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    public function getOrderDataLayer($cartId, $orderId)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $order = $this->orderRepository->get($orderId);
        if ($order->getQuoteId() != $quoteIdMask->getQuoteId()) {
            throw new NoSuchEntityException(__('No such entity'));
        }
        return $this->gtmDataLayer->getOrderDataLayer($order);
    }
}
