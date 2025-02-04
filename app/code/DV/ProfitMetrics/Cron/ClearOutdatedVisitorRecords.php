<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Cron;

class ClearOutdatedVisitorRecords
{
    /**
     * @var \DV\ProfitMetrics\Model\Config\Settings
     */
    private $settings;
    /**
     * @var \DV\ProfitMetrics\Model\ResourceModel\Visitor\CollectionFactory
     */
    private $visitorCollectionFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    public function __construct(
        \DV\ProfitMetrics\Model\Config\Settings $settings,
        \DV\ProfitMetrics\Model\ResourceModel\Visitor\CollectionFactory $visitorCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->settings = $settings;
        $this->visitorCollectionFactory = $visitorCollectionFactory;
        $this->date = $date;
    }

    public function execute(): void
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $visitorCollection */
        $visitorCollection = $this->visitorCollectionFactory->create();
        $timeToLive = $this->settings->getTrackingDataLifetimeDays();
        $visitorTimestampThreshold = $this->date->timestamp() - $timeToLive * 24 * 60 * 60;
        $visitorCollection->addFieldToFilter('timestamp', ['lteq' => $visitorTimestampThreshold]);
        $deleteQueryExpression = $visitorCollection->getSelect()->deleteFromSelect('main_table');
        $visitorCollection->getConnection()->query($deleteQueryExpression);
    }
}
