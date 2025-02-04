<?php

namespace Murergrej\Tracking\Plugin\Model\Order;

class Track
{
    public function afterGetNumberDetail(\Magento\Shipping\Model\Order\Track $subject, $result)
    {
        if ($result instanceof \Magento\Framework\DataObject && $result->getData('set_title')) {
            $result->setData('carrier_title', $subject->getTitle());
        }
        return $result;
    }
}
