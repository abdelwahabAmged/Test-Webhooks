<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

namespace DV\ProfitMetrics\Model\Config\Source;

/**
 * Class Frequency
 * @package DV\ProfitMetrics\Model\Config\Source
 */
class Frequency implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected static $_options;

    const CRON_1_MINUTE = '1';

    const CRON_2_MINUTES = '2';

    const CRON_5_MINUTES = '5';

    const CRON_15_MINUTES = '15';

    const CRON_TWICE_HOUR = '30';

    const CRON_HOURLY = 'H';

    const CRON_DAILY = 'D';

    const CRON_WEEKLY = 'W';

    const CRON_MONTHLY = 'M';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = [
                ['label' => __('Every minute'), 'value' => self::CRON_1_MINUTE],
                ['label' => __('Every 2 minutes'), 'value' => self::CRON_2_MINUTES],
                ['label' => __('Every 5 minutes'), 'value' => self::CRON_5_MINUTES],
                ['label' => __('Every 15 minutes'), 'value' => self::CRON_15_MINUTES],
                ['label' => __('Twice a hour'), 'value' => self::CRON_TWICE_HOUR],
                ['label' => __('Hourly'), 'value' => self::CRON_HOURLY],
                ['label' => __('Daily'), 'value' => self::CRON_DAILY],
                ['label' => __('Weekly'), 'value' => self::CRON_WEEKLY],
                ['label' => __('Monthly'), 'value' => self::CRON_MONTHLY],
            ];
        }
        return self::$_options;
    }
}
