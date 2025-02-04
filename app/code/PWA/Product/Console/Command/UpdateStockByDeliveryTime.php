<?php

namespace PWA\Product\Console\Command;
use Magento\Framework\App\State;
use Magento\Store\Model\App\Emulation;
use PWA\Product\Model\DeliveryTimeStockUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateStockByDeliveryTime extends Command
{
    /**
     * @var DeliveryTimeStockUpdater
     */
    protected $updater;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var State
     */
    protected $state;

    public function __construct(
        DeliveryTimeStockUpdater $updater,
        Emulation $emulation,
        State $state,
        string $name = null
    ) {
        $this->updater = $updater;
        $this->emulation = $emulation;
        $this->state = $state;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('delivery-time:update-stock');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->emulation->startEnvironmentEmulation(0, \Magento\Framework\App\Area::AREA_ADMINHTML);
        try {
            $this->updater->setOutput($output);
            $this->updater->execute();
        } finally {
            $this->emulation->stopEnvironmentEmulation();
        }
    }
}
