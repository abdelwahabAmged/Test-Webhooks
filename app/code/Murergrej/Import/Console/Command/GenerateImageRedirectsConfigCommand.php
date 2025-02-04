<?php

namespace Murergrej\Import\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Api\ProductMediaAttributeManagementInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\GalleryManagement;

class GenerateImageRedirectsConfigCommand extends Command
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var ProductMediaAttributeManagementInterface
     */
    protected $mediaAttribute;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var GalleryManagement
     */
    protected $galleryManagement;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        ProductMediaAttributeManagementInterface $productMediaAttributeManagement,
        AttributeSetRepositoryInterface $attributeSetRepository,
        GalleryManagement $galleryManagement,
        string $name = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productResource = $productResource;
        $this->mediaAttribute = $productMediaAttributeManagement;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->galleryManagement = $galleryManagement;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('murergrej:import:catalog:product:images:generate-redirects-config')
            ->addOption('import_file', 'i', InputOption::VALUE_REQUIRED, 'File with images to import (only product skus are used)')
            ->addOption('sku_column', null, InputOption::VALUE_REQUIRED, 'Sku column index (from 0)', 0)
            ->addArgument('output', InputArgument::REQUIRED, 'Output file with redirects config.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->collectionFactory->create();

        $skus = $this->getProductSkus($input);
        if ($skus) {
            $collection->addAttributeToFilter('sku', ['in' => $skus]);
        }

        $file = $input->getArgument('output');
        $handle = fopen($file, 'w');
        if (!$handle) {
            throw new \Exception('Could not open file ' . $file);
        }

        try {
            $count = count($collection->getItems());
            $i = 0;
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($collection as $product) {
                $output->writeln(++$i . '/' . $count . ' ' . $product->getSku());
                $entries = $this->galleryManagement->getList($product->getSku());
                foreach ($entries as $entry) {
                    if ($entry->getMediaType() != 'image') {
                        continue;
                    }

                    fputcsv($handle, [
                        ltrim($entry->getFile(), '/'),
                        $product->getId(),
                        implode(',', $entry->getTypes())
                    ]);
                }
            }
        } finally {
            fclose($handle);
        }
    }

    protected function getProductIds(InputInterface $input)
    {
        $importFile = $input->getOption('import_file');
        if (!$importFile) {
            return null;
        }
        $handle = fopen($importFile, 'r');
        if (!$handle) {
            throw new \Exception('Could not open file ' . $importFile);
        }

        $skuIndex = $input->getOption('sku_column');
        $header = fgetcsv($handle);
        if ($header === false) {
            throw new \Exception('File is empty');
        }

        $ids = [];
        while(($line = fgetcsv($handle)) !== false) {
            $sku = $line[$skuIndex];
            $id = $this->productResource->getIdBySku($sku);
            if ($id) {
                $ids[] = $id;
            }
        }

        fclose($handle);

        return $ids;
    }

    protected function getProductSkus(InputInterface $input)
    {
        $importFile = $input->getOption('import_file');
        if (!$importFile) {
            return null;
        }
        $handle = fopen($importFile, 'r');
        if (!$handle) {
            throw new \Exception('Could not open file ' . $importFile);
        }

        $skuIndex = $input->getOption('sku_column');
        $header = fgetcsv($handle);
        if ($header === false) {
            throw new \Exception('File is empty');
        }

        $skus = [];
        while(($line = fgetcsv($handle)) !== false) {
            if (!empty($line[$skuIndex])) {
                $skus[] = $line[$skuIndex];
            }
        }

        fclose($handle);

        return $skus;
    }
}
