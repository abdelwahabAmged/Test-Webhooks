<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

namespace DV\ProfitMetrics\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ProductsPrice
 * @package DV\ProfitMetrics\Model\Config\Source
 */
class ProductsPrice implements ArrayInterface
{
    const CONFIGURABLE_PRICE_SOURCE_CONFIGURABLE = 1;
    const CONFIGURABLE_PRICE_SOURCE_MIN_SIMPLE = 2;

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CONFIGURABLE_PRICE_SOURCE_CONFIGURABLE, 'label' => __('Get from parent configurable product')],
            ['value' => self::CONFIGURABLE_PRICE_SOURCE_MIN_SIMPLE, 'label' => __('Calculate the minimum from related simple options')]
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::CONFIGURABLE_PRICE_SOURCE_CONFIGURABLE => __('Get from parent configurable product'),
            self::CONFIGURABLE_PRICE_SOURCE_MIN_SIMPLE => __('Calculate the minimum from related simple options')
        ];
    }
}
