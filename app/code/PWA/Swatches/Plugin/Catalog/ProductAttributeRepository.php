<?php

namespace PWA\Swatches\Plugin\Catalog;

use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Model\Swatch;
use PWA\Swatches\Api\Data\SwatchInterfaceFactory;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;

class ProductAttributeRepository
{
    /**
     * @var SwatchCollectionFactory
     */
    protected $swatchCollectionFactory;

    /**
     * @var SwatchData
     */
    protected $swatchHelper;

    protected $swatchInterfaceFactory;

    public function __construct(SwatchCollectionFactory $swatchCollectionFactory, SwatchData $swatchHelper, SwatchInterfaceFactory $swatchInterfaceFactory)
    {
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->swatchHelper = $swatchHelper;
        $this->swatchInterfaceFactory = $swatchInterfaceFactory;
    }

    public function afterGetList(\Magento\Catalog\Api\ProductAttributeRepositoryInterface $subject, \Magento\Eav\Model\AttributeSearchResults $result)
    {
        foreach ($result->getItems() as $item) {
            if (!$item instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute) {
                continue;
            }
            if ($item->getFrontendInput() != 'select') {
                continue;
            }
            $additionalData = $item->getAdditionalData();
            if (!$additionalData) {
                continue;
            }
            $additionalData = json_decode($additionalData, true);
            if (!$additionalData || $additionalData['swatch_input_type'] != 'visual') {
                continue;
            }

            $options = $item->getOptions();
            if (!$options) {
                continue;
            }

            $allOptionIds = array_map(function ($option) {
                return $option->getValue();
            }, $options);
            $swatchesData = $this->swatchHelper->getSwatchesByOptionsId($allOptionIds);
            if (!$swatchesData) {
                continue;
            }
            $item->getExtensionAttributes()->setSwatches(array_map(function ($data) {
                return $this->swatchInterfaceFactory->create()->setData($data);
            }, $swatchesData));
        }

        return $result;
    }
}
