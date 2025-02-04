<?php

namespace Murergrej\Customer\Model\ResourceModel\Order\Customer;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Customer\Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addAttributeToSelect('company_name');
        return $this;
    }
}
