<?php

namespace Murergrej\Sales\Block\Adminhtml\Order\Create\Billing;

class Address extends \Magento\Sales\Block\Adminhtml\Order\Create\Billing\Address
{
    public function getDontSaveInAddressBook()
    {
        return true;
    }
}
