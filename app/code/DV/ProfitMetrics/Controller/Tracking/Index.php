<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Controller\Tracking;

use DV\ProfitMetrics\Model\ProfitHelper;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \DV\ProfitMetrics\Observer\ControllerActionPredispatch
     */
    private $predispatchObserver;

    /**
     * Index constructor.
     * @param \DV\ProfitMetrics\Observer\ControllerActionPredispatch $predispatchObserver
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \DV\ProfitMetrics\Observer\ControllerActionPredispatch $predispatchObserver,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->predispatchObserver = $predispatchObserver;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute(): \Magento\Framework\App\ResponseInterface
    {
        $trackingData = $this->predispatchObserver->getTrackingDataFromRequest();
        $visitorId = $this->customerSession->getData(ProfitHelper::PROFITMETRICS_VISITOR_ID_SESSION_KEY);
        $lastUpdateTimestamp = $this->customerSession->getData(ProfitHelper::PROFITMETRICS_UPDATE_TIMESTAMP_SESSION_KEY);

        if (!$visitorId || $trackingData['timestamp'] !== $lastUpdateTimestamp) {
            $lastUpdateTimestamp = '';
        }

        return $this->getResponse()->setBody((string) $lastUpdateTimestamp);
    }
}
