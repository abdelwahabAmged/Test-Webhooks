<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Model\Source;

class Days implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Sunday')],
            ['value' => '1', 'label' => __('Monday')],
            ['value' => '2', 'label' => __('Tuesday')],
            ['value' => '3', 'label' => __('Wednesday')],
            ['value' => '4', 'label' => __('Thursday')],
            ['value' => '5', 'label' => __('Friday')],
            ['value' => '6', 'label' => __('Saturday')],
            
        ];
    }
}
