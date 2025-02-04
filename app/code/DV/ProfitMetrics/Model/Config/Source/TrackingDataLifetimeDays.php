<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Model\Config\Source;

class TrackingDataLifetimeDays implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var array
     */
    protected static $_options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!isset(self::$_options)) {
            self::$_options = [
                ['label' => __('30 days'), 'value' => 30],
                ['label' => __('60 days'), 'value' => 60],
                ['label' => __('90 days'), 'value' => 90],
            ];
        }

        return self::$_options;
    }
}
