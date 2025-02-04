<?php

namespace PWA\Profitmetrics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\ResourceModel\Order\Payment\Collection;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class SalesOrderPaymentCollectionAddPaymentMethodTitleObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var Collection $collection */
            $collection = $observer->getOrderPaymentCollection();
            /** @var Payment $payment */
            foreach ($collection as $payment) {
                $payment->getExtensionAttributes()->setMethodTitle($payment->getMethodInstance()->getTitle());
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
