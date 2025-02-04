<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Model;

class OrderSend
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $connection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \DV\ProfitMetrics\Model\Config\Settings
     */
    private $profitMetricsSettings;

    /**
     * @var \DV\ProfitMetrics\Model\ProfitMetricsApiOrdersSender
     */
    private $profitmetricsApiOrdersSender;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * OrderSend constructor.
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \DV\ProfitMetrics\Model\Config\Settings $profitMetricsSettings
     * @param \DV\ProfitMetrics\Model\ProfitMetricsApiOrdersSender $profitmetricsApiOrdersSender
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \DV\ProfitMetrics\Model\Config\Settings $profitMetricsSettings,
        \DV\ProfitMetrics\Model\ProfitMetricsApiOrdersSender $profitmetricsApiOrdersSender,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->connection = $connection;
        $this->date = $date;
        $this->profitMetricsSettings = $profitMetricsSettings;
        $this->profitmetricsApiOrdersSender = $profitmetricsApiOrdersSender;
        $this->logger = $logger;
    }


    public function sendNewOrders(): void
    {
        if (!$this->profitMetricsSettings->getProfitMetricsId()) {
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        foreach($this->getOrdersToSend() as $order) {
            try {
                $this->profitmetricsApiOrdersSender->sendOrderData($order);
                $this->updateOrderSetProfitMetricsSentDate($order);
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage(), [
                    'trace' => $exception->getTraceAsString()
                ]);
            }
        }
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private function getOrdersToSend(): \Magento\Sales\Model\ResourceModel\Order\Collection
    {
        $orderStatusesToSend = $this->profitMetricsSettings->getOrderStatusesToSend();
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('main_table.profitmetrics_visitor_id', ['notnull' => true]);
        $orderCollection->addFieldToFilter('main_table.profitmetrics_sent_date', ['null' => true]);
        $orderCollection->addFieldToFilter('main_table.status', ['in' => $orderStatusesToSend]);
        $orderCollection->join(
            ['visitor' => $orderCollection->getTable('dv_profitmetrics_visitor')],
            'visitor.entity_id = main_table.profitmetrics_visitor_id',
            ['gacid', 'gacid_source', 'gclid', 'fbp', 'fbc', 'cua', 'cip', 't']
        );

        return $orderCollection;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    private function updateOrderSetProfitMetricsSentDate(\Magento\Sales\Model\Order $order): void
    {
        try {
            $connection = $this->connection->getConnection();
            $connection->update(
                $this->connection->getTableName('sales_order'),
                ['profitmetrics_sent_date' => $this->date->gmtDate()],
                'entity_id = ' . $order->getId()
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ]);
        }
    }
}
