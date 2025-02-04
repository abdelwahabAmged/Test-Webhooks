<?php

namespace PWA\Product\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class DelieryTimeDate  implements ObserverInterface
{
    /**
     * @var \PWA\Product\Helper\Data
     */
    protected $helper;

    public function __construct(\PWA\Product\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        $date = $this->helper->getDateTime($product->getDeliveryTime());
        if ($date) {
            $dateStr = $date->format('Y-m-d H:i:s');
        } else {
            $dateStr = null;
        }
        $product->setDeliveryTimeDate($dateStr);
    }
}
