<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Observer;

use DV\ProfitMetrics\Model\ProfitHelper;
use DV\ProfitMetrics\Observer\ControllerActionPredispatch as ControllerActionPredispatch;

class OrderSaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * OrderSaveBefore constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $order = $observer->getData('order');
        $profitMetricsVisitorId = $this->customerSession->getData(
            ProfitHelper::PROFITMETRICS_VISITOR_ID_SESSION_KEY
        );

        if ($order && $profitMetricsVisitorId && !$order->getData('profitmetrics_visitor_id')) {
            $order->setData('profitmetrics_visitor_id', $profitMetricsVisitorId);
        }
    }
}
