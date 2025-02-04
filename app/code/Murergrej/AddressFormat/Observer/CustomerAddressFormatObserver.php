<?php

namespace Murergrej\AddressFormat\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Address;

class CustomerAddressFormatObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var Address $address */
        $address = $observer->getData('address');
        $street = $address->getData('street');
        if (is_string($street)) {
            $street = str_replace("\r\n", "\n", $street);
            $address->setData('street', $street);
        }
    }
}
