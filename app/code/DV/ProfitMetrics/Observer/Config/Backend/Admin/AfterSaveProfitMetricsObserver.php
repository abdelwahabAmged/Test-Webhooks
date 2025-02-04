<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

declare(strict_types=1);

namespace DV\ProfitMetrics\Observer\Config\Backend\Admin;

use DV\ProfitMetrics\Model\Config\Settings;
use DV\ProfitMetrics\Model\Config\Source\Frequency as SourceFrequency;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AfterSaveProfitMetricsObserver
 * @package DV\ProfitMetrics\Observer\Config\Backend\Admin
 */
class AfterSaveProfitMetricsObserver implements ObserverInterface
{
    /**
     * Cron string path
     */
    private const CRON_STRING_PATH = 'crontab/default/jobs/profit_metrics/schedule/cron_expr';
    private const ORDER_SAVE_CRON_STRING_PATH = 'crontab/default/jobs/profit_metrics_orders_send/schedule/cron_expr';

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * AfterSaveProfitMetricsObserver constructor.
     * @param Settings $settings
     * @param ValueFactory $configValueFactory
     */
    public function __construct(
        Settings $settings,
        ValueFactory $configValueFactory
    ) {
        $this->settings = $settings;
        $this->configValueFactory = $configValueFactory;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer): void
    {
        if (!$this->settings->isEnabled()) {
            return;
        }

        $this->saveCronFrequency(
            $this->settings->getCronTime(),
            $this->settings->getCronFrequency(),
            self::CRON_STRING_PATH
        );

        $this->saveCronFrequency(
            $this->settings->getOrderSendCronTime(),
            $this->settings->getOrderSendCronFrequency(),
            self::ORDER_SAVE_CRON_STRING_PATH
        );
    }

    /**
     * @param string $time
     * @param string $frequency
     * @param string $configKey
     * @throws \Exception
     */
    private function saveCronFrequency(string $time, string $frequency, string $configKey): void
    {
        $time = explode(',', $time);

        $cronExprArray = [
            $this->getMinute($frequency, $time),
            $this->getHour($frequency, $time),
            $frequency === SourceFrequency::CRON_DAILY ? '*/1' : '*',
            $frequency === SourceFrequency::CRON_MONTHLY ? '1' : '*',
            $frequency === SourceFrequency::CRON_WEEKLY ? '*/7' : '*',
        ];

        $cronExprString = implode(' ', $cronExprArray);

        try {
            $this->configValueFactory->create()->load(
                $configKey,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                $configKey
            )->save();

        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the cron expression.'));
        }
    }

    /**
     * @param string $frequency
     * @param array $time
     * @return string
     */
    private function getMinute(string $frequency, array $time): string
    {
        $minute = '*';

        if (count($time) > 1 && (int)$time[1] !== 0 && $this->isCanSetTime($frequency)) {
            $minute = (int)$time[1];
        } else {
            switch ($frequency) {
                case SourceFrequency::CRON_HOURLY :
                    $minute = '0';
                    break;
                case SourceFrequency::CRON_TWICE_HOUR :
                    $minute = '*/30';
                    break;
                case SourceFrequency::CRON_15_MINUTES:
                    $minute = '*/15';
                    break;
                case SourceFrequency::CRON_5_MINUTES:
                    $minute = '*/5';
                    break;
                case SourceFrequency::CRON_2_MINUTES:
                    $minute = '*/2';
                    break;
                case SourceFrequency::CRON_1_MINUTE:
                    $minute = '*/1';
                    break;
            }
        }

        return (string) $minute;
    }

    /**
     * @param string $frequency
     * @return bool
     */
    private function isCanSetTime(string $frequency): bool
    {
        return $frequency === SourceFrequency::CRON_DAILY
            || $frequency === SourceFrequency::CRON_WEEKLY
            ||$frequency === SourceFrequency::CRON_MONTHLY;
    }

    /**
     * @param string $frequency
     * @param array $time
     * @return string
     */
    private function getHour(string $frequency, array $time): string
    {
        $hour = '*';

        if (count($time) > 0 && (int)$time[0] !== 0 && $this->isCanSetTime($frequency)) {
            $hour = (int)$time[0];
        }

        return (string) $hour;
    }
}
