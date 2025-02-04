<?php

namespace Murergrej\Import\Console\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Catalog\Model\Product\Gallery\Processor;
use \Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class UpdateProductImagesCommand extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Gallery
     */
    protected $gallery;

    /**\
     * @var Processor
     */
    protected $processor;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var string|null
     */
    protected $tmpPath = null;

    /**
     * @var int[]|null
     */
    protected $mediaAttributeIds = null;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(
        State $state,
        ProductRepositoryInterface $productRepository,
        Gallery $gallery,
        Processor $processor,
        Filesystem $filesystem,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\App\ResourceConnection $resource,
        string $name = null
    ) {
        $this->state = $state;
        $this->productRepository = $productRepository;
        $this->gallery = $gallery;
        $this->processor = $processor;
        $this->filesystem = $filesystem;
        $this->eavAttribute = $eavAttribute;
        $this->resource = $resource;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('murergrej:import:catalog:product:images')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file with skus and image paths')
            ->addArgument('images_dir', InputArgument::REQUIRED, 'Path to catalog with images')
            ->addOption('sku_column', null, InputOption::VALUE_REQUIRED, 'Sku column index (from 0)', 0)
            ->addOption('main_image_column', null, InputOption::VALUE_REQUIRED, 'Product image column index (from 0)', 1)
            ->addOption('secondary_image_column', null, InputOption::VALUE_REQUIRED, 'Secondary image column index (from 0)', 5)
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        $file = $input->getArgument('file');
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new \Exception('Could not open file ' . $file);
        }

        $config = (object)[
            'base_dir' => $input->getArgument('images_dir'),
            'sku_index' => $input->getOption('sku_column'),
            'image1_index' => $input->getOption('main_image_column'),
            'image2_index' => $input->getOption('secondary_image_column')
        ];

        try {
            $header = fgetcsv($handle);
            if ($header === false) {
                return;
            }

            while (($line = fgetcsv($handle)) !== false) {
                $this->importLine($line, $config);
            }
        } finally {
            fclose($handle);
        }
    }

    protected function importLine($line, $config)
    {
        $sku = $line[$config->sku_index];
        $image1 = $line[$config->image1_index];
        $image2 = $line[$config->image2_index];

        $this->output->writeln($sku);

        try {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->get($sku, false, 0);
        } catch (NoSuchEntityException $e) {
            $this->output->writeln('Product not found.');
            return;
        }

        if (empty($image1) && empty($image2)) {
            $this->output->writeln('Images are not specified.');
            return;
        }

        $images = $product->getMediaGalleryImages();
        foreach ($images as $image) {
            if ($image->getMediaType() != 'image') {
                continue;
            }
            $this->gallery->deleteGallery($image->getValueId());
            $this->processor->removeImage($product, $image->getFile());
        }

        if (!empty($image1)) {
            $this->addImageToMediaGallery($product, $config->base_dir . '/' . $image1, ['image', 'small_image', 'thumbnail']);
        }
        if (!empty($image2)) {
            if ($image1 == $image2) {
                $this->output->writeln('Secondary image is duplicate of main image.');
            } else {
                $this->addImageToMediaGallery($product, $config->base_dir . '/' . $image2);
            }
        }
        $product->save();
        $this->deleteStoreScopeMediaAttributes($product->getId());
    }

    protected function addImageToMediaGallery(\Magento\Catalog\Model\Product $product, $file, $mediaAttribute = null)
    {
        $preparedFile = $this->prepareFile($file);
        if (!$preparedFile) {
            return false;
        }
        $product->addImageToMediaGallery($preparedFile, $mediaAttribute, true, false);
        return true;
    }

    protected function prepareFile($fullPath)
    {
        if (!is_file($fullPath)) {
            $this->output->writeln('File ' . $fullPath . ' does not exist');
            return false;
        }
        $pathinfo = pathinfo($fullPath);
        $destinationPath = $this->getTmpPath() . '/' . $pathinfo['basename'];

        if (is_file($destinationPath)) {
            $i = 0;
            do {
                $destinationDir = $this->getTmpPath() . '/import' . ($i++);
                $destinationPath = $destinationDir . '/' . $pathinfo['basename'];
            } while (is_file($destinationPath));
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir);
            }
        }
        try {
            $result = copy($fullPath, $destinationPath);
        } catch (\Exception $e) {
            $result = false;
        }
        if (!$result) {
            $this->output->writeln('Could not copy file ' . $fullPath . ' to ' . $destinationPath);
            return false;
        }
        return $destinationPath;
    }

    protected function getTmpPath()
    {
        if (!$this->tmpPath) {
            $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $tmpPath = $mediaPath . 'tmp';
            if (!is_dir($tmpPath)) {
                mkdir($tmpPath);
            }
            $this->tmpPath = $tmpPath;
        }
        return $this->tmpPath;
    }

    protected function deleteStoreScopeMediaAttributes($productId)
    {
        $attributeIds = $this->getMediaAttrubteIds();
        if (!$attributeIds) {
            return;
        }
        $connection = $this->resource->getConnection();
        $result = $connection->delete($this->resource->getTableName('catalog_product_entity_varchar'), [
            'entity_id = ?' => $productId,
            'attribute_id IN (?)' => $attributeIds,
            'store_id != ?' => 0
        ]);
        if ($result) {
            $this->output->writeln('Deleted media attributes from not default scope: ' . $result);
        }
    }

    protected function getMediaAttrubteIds()
    {
        if (is_null($this->mediaAttributeIds)) {
            $codes = ['image', 'small_image', 'thumbnail'];
            $mediaAttributeIds = [];
            foreach ($codes as $code) {
                $id = $this->eavAttribute->getIdByCode('catalog_product', $code);
                if ($id) {
                    $mediaAttributeIds[] = $id;
                }
            }
            $this->mediaAttributeIds = $mediaAttributeIds;
        }
        return $this->mediaAttributeIds;
    }
}
