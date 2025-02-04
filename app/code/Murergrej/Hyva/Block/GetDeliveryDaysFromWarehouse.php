<?php

namespace Murergrej\Hyva\Block;

use Magento\Framework\View\Element\Template;
use Murergrej\Hyva\Helper\DeliveringDaysFromWarehouse;

class GetDeliveryDaysFromWarehouse extends Template
{
    protected $dateHelper;

    public function __construct(
        Template\Context $context,
        DeliveringDaysFromWarehouse $dateHelper,
        array $data = []
    ) {
        $this->dateHelper = $dateHelper;
        parent::__construct($context, $data);
    }

    public function getRemainingDays($product)
    {
        $deliveryTime = $product->getDeliveryTime();
        $defaultDeliveryTime = $product->getData('default_delivery_time');

        return $this->dateHelper->getRemainingDays($deliveryTime, $defaultDeliveryTime);
    }
}
