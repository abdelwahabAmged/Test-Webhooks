<?php

namespace Murergrej\Import\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Gallery\Processor as GalleryProcessor;
use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductImageDuplicatesFixer
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var GalleryProcessor
     */
    protected $galleryProcessor;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(
        CollectionFactory $collectionFactory,
        GalleryProcessor $galleryProcessor,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->galleryProcessor = $galleryProcessor;
        $this->productRepository = $productRepository;
        $this->resource = $resource;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $pageSize = 250;
        $page = 0;
        $size = $collection->getSize();
        $mediaAttributeCodes = $this->galleryProcessor->getMediaAttributeCodes();
        $this->writeln('size ' . $size);

        $varcharTable = $this->resource->getTableName('catalog_product_entity_varchar');
        $connection = $this->resource->getConnection();
        $mediaAttributeIdsByCodes = $this->getProductAttributeIdsByCodes($mediaAttributeCodes, $connection);
        $mediaAttributeCodesByIds = [];
        foreach ($mediaAttributeIdsByCodes as $code => $id) {
            $mediaAttributeCodesByIds[$id] = $code;
        }

        while ($page * $pageSize < $size) {
            $this->writeln('page ' . $page);
            $collection->clear();
            $collection->setPage(++$page, $pageSize);
            $collection->load();
            /** @var Product $product */
            foreach ($collection->load() as $_product) {
                $this->writeln('product ' . $_product->getId());
                $product = $this->productRepository->getById($_product->getId(), false, 0);
                $images = $product->getMediaGalleryImages();
                $files = array_map(function ($image) {
                    return $image->getFile();
                }, $images->getItems());
                $update = [];
                $preserveImages = [];
                /** @var string[] $notExistingOriginalFiles */
                $notExistingOriginalFiles = [];
                /** @var string[] $deleteImages */
                $deleteImages = [];
                foreach ($images as $image) {
                    $file = $image->getFile();
                    if ($file == 'no_selection') {
                        continue;
                    }
                    $pathinfo = pathinfo($file);
                    if (!isset($pathinfo['extension'])) {
                        continue;
                    }
                    $filename = $pathinfo['filename'];
                    if (preg_match('/^(.*)_\d+$/', $filename, $matches)) {
                        $originalFile = $pathinfo['dirname'] . '/' . $matches[1] . '.' . $pathinfo['extension'];
                        if (in_array($originalFile, $files)) {
                            $deleteImages[] = $file;
                            foreach ($mediaAttributeCodes as $attributeCode) {
                                if ($product->getData($attributeCode) == $file) {
                                    $update[$attributeCode] = $originalFile;
                                }
                            }
                        } else if (array_key_exists($originalFile, $notExistingOriginalFiles)) {
                            $deleteImages[] = $file;
                            foreach ($mediaAttributeCodes as $attributeCode) {
                                if ($product->getData($attributeCode) == $file) {
                                    $update[$attributeCode] = $notExistingOriginalFiles[$originalFile];
                                }
                            }
                        } else {
                            $notExistingOriginalFiles[$originalFile] = $file;
                            $preserveImages[] = $file;
                        }
                    } else {
                        $preserveImages[] = $file;
                    }
                }
                foreach ($deleteImages as $image) {
                    $this->writeln('Delete image ' . $image);
                    $this->galleryProcessor->removeImage($product, $image);
                }
                if (!empty($update)) {
                    $this->writeln('Update attributes: ');
                    foreach ($update as $field => $value) {
                        $this->writeln($field . ' => ' . $value);
                    }
                    $product->addData($update);
                }

                if (!empty($deleteImages) || !empty($update)) {
                    $product->save();

                    // clear store scoped values
                    $attributeIds = [];
                    foreach ($update as $attributeCode => $value) {
                        $attributeIds[] = $mediaAttributeIdsByCodes[$attributeCode];
                    }
                    if (!empty($attributeIds)) {
                        $select = $connection->select()
                            ->from($varcharTable)
                            ->where('store_id != ?', 0)
                            ->where('entity_id = ?', $product->getId())
                            ->where('attribute_id IN (?)', $attributeIds);
                        $values = $connection->fetchAll($select);
                        $deleteValues = [];
                        foreach ($values as $value) {
                            $file = $value['value'];
                            if (in_array($file, $preserveImages) || $file == 'no_selection') {
                                continue;
                            }

                            $pathinfo = pathinfo($file);
                            if (!isset($pathinfo['extension'])) {
                                continue;
                            }
                            $filename = $pathinfo['filename'];
                            if (preg_match('/^(.*)_\d+$/', $filename, $matches)) {
                                $originalFile = $pathinfo['dirname'] . '/' . $matches[1] . '.' . $pathinfo['extension'];
                                if (in_array($originalFile, $preserveImages)) {
                                    $this->writeln($mediaAttributeCodesByIds[$value['attribute_id']] . ':' . $value['store_id'] . ' => ' . $originalFile);
                                    $connection->update($varcharTable, [
                                        'value' => $originalFile
                                    ], [
                                        'value_id = ?' => $value['value_id']
                                    ]);
                                    continue;
                                }
                            }

                            $this->writeln('Delete value ' . $mediaAttributeCodesByIds[$value['attribute_id']] . ':' . $value['store_id'] . ' = ' . $originalFile);
                            $deleteValues[] = $value['value_id'];
                        }
                        if (!empty($deleteValues)) {
                            $connection->delete($varcharTable, [
                                'value_id IN (?)' => $deleteValues
                            ]);
                        }
                        /*$connection->delete($varcharTable, [
                            'store_id != ?' => 0,
                            'entity_id = ?' => $product->getId(),
                            'attribute_id IN (?)' => $attributeIds
                        ]);*/
                    }
                }
            }
        }
    }

    /**
     * @param string [] $codes
     * @param AdapterInterface $connection
     * @return int[]
     */
    protected function getProductAttributeIdsByCodes($codes, AdapterInterface $connection)
    {
        $select = $connection->select()
            ->from($this->resource->getTableName('eav_attribute'), ['attribute_id', 'attribute_code'])
            ->where('entity_type_id = ?', 4)
            ->where('attribute_code IN (?)', $codes);
        $rows = $connection->fetchAll($select);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['attribute_code']] = $row['attribute_id'];
        }
        return $result;
    }

    protected function writeln($message)
    {
        if ($this->output) {
            $this->output->writeln($message);
        }
    }
}
