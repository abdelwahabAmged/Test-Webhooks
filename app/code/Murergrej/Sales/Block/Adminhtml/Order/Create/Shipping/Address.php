<?php

namespace Murergrej\Sales\Block\Adminhtml\Order\Create\Shipping;

class Address extends \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Address
{
    public function getDontSaveInAddressBook()
    {
        return true;
    }
}
