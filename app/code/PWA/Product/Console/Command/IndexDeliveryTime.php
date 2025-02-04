<?php

namespace PWA\Product\Console\Command;
use Magento\Framework\App\State;
use Magento\Store\Model\App\Emulation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class IndexDeliveryTime extends Command
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \PWA\Product\Helper\Data
     */
    protected $helper;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var State
     */
    protected $state;

    public function __construct(
        CollectionFactory $collectionFactory,
        \PWA\Product\Helper\Data $helper,
        Emulation $emulation,
        State $state,
        string $name = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->emulation = $emulation;
        $this->state = $state;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('delivery-time:reindex')
            ->addArgument('product_id', InputArgument::IS_ARRAY, 'Product Id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->emulation->startEnvironmentEmulation(0, \Magento\Framework\App\Area::AREA_ADMINHTML);
        try {
            $this->index($input->getArgument('product_id'), $output);
        } finally {
            $this->emulation->stopEnvironmentEmulation();
        }
    }

    protected function index($ids, OutputInterface $output)
    {
        $collectionUpdate = $this->collectionFactory->create();
        $collectionClear = $this->collectionFactory->create();
        if (!empty($ids)) {
            $collectionUpdate->addFieldToFilter('entity_id', ['in' => $ids]);
            $collectionClear->addFieldToFilter('entity_id', ['in' => $ids]);
        }
        $collectionUpdate->addAttributeToFilter('delivery_time', ['neq' => '']);
        $collectionUpdate->addAttributeToSelect('delivery_time_date');
        $collectionClear
            ->addAttributeToFilter('delivery_time_date', ['notnull' => true])
            ->addAttributeToFilter('delivery_time', [
                ['eq' => ''],
                ['null' => true]
            ]);

        $output->writeln('Update ' . $collectionUpdate->count() . ' products.');
        $i = 0;
        foreach ($collectionUpdate as $product) {
            $date = $this->helper->getDateTime($product->getDeliveryTime());
            if ($date) {
                $dateStr = $date->format('Y-m-d H:i:s');
            } else {
                $dateStr = null;
            }
            $output->writeln(++$i . '. ' . $product->getSku() . ' ' . ($dateStr ?: 'NULL'));
            if ($product->getDeliveryTimeDate() != $dateStr) {
                $product->setDeliveryTimeDate($dateStr);
                $product->save();
            }
        }

        $output->writeln('Clear ' . $collectionClear->count() . ' products.');
        $i = 0;
        foreach ($collectionClear as $product) {
            $output->writeln(++$i . '. ' . $product->getSku());
            $product->setDeliveryTimeDate(null);
            $product->save();
        }
    }
}
