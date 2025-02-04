<?php

namespace PWA\Base\Model\Config\Source;

class ProductUrlMode implements \Magento\Framework\Option\ArrayInterface
{
    const MODE_TOP_CATEGORY = 'top_category';
    const MODE_MAX_LEVEL = 'max_level';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::MODE_TOP_CATEGORY, 'label' => __('Top Category')],
            ['value' => self::MODE_MAX_LEVEL, 'label' => __('Max Level Category')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::MODE_TOP_CATEGORY => __('Top Category'),
            self::MODE_MAX_LEVEL => __('Max Level Category')
        ];
    }
}
