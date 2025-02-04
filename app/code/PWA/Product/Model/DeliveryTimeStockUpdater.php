<?php

namespace PWA\Product\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeliveryTimeStockUpdater
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    protected $getSourceItemsBySku;

    /**
     * @var SourceItemsSaveInterface
     */
    protected $sourceItemsSave;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(
        CollectionFactory $collectionFactory,
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        SourceItemsSaveInterface $sourceItemsSave
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->sourceItemsSave = $sourceItemsSave;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $yesterday = new \DateTime();
        $yesterday->setTime(0,0, 0);
        $yesterday->modify('-1 day');
        $collection
            ->addAttributeToFilter('delivery_time_date', ['gt' => $yesterday->format('Y-m-d')])
            ->addAttributeToFilter('delivery_time_date', ['lte' => $today->format('Y-m-d')]);
        $collection->joinTable(
            'cataloginventory_stock_item',
            'product_id=entity_id',
            ['is_in_stock'],
            ['website_id' => '0', 'stock_id' => '1']
        );
        $collection->getSelect()->where('is_in_stock = ?', 0);
        foreach ($collection as $product) {
            if ($this->output) {
                $this->output->writeln($product->getSku());
            }
            $sourceItems = $this->getSourceItemsBySku->execute($product->getSku());
            foreach ($sourceItems as $sourceItem) {
                $sourceItem->setStatus(1);
            }
            $this->sourceItemsSave->execute($sourceItems);
        }
    }
}
