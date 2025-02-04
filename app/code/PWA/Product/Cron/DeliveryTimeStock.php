<?php

namespace PWA\Product\Cron;

use Magento\Store\Model\App\Emulation;
use PWA\Product\Model\DeliveryTimeStockUpdater;

class DeliveryTimeStock
{
    /**
     * @var DeliveryTimeStockUpdater
     */
    protected $updater;

    /**
     * @var Emulation
     */
    protected $emulation;

    public function __construct(
        DeliveryTimeStockUpdater $updater,
        Emulation $emulation
    )
    {
        $this->updater = $updater;
        $this->emulation = $emulation;
    }

    public function execute()
    {
        $this->emulation->startEnvironmentEmulation(0, \Magento\Framework\App\Area::AREA_ADMINHTML);
        try {
            $this->updater->execute();
        } finally {
            $this->emulation->stopEnvironmentEmulation();
        }
    }
}
