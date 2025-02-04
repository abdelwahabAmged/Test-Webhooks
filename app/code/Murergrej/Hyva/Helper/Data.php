<?php
/**
 * @category Murergrej
 * @package Murergrej_Hyva
 * @author Jorgena Shinjatari info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
*/

declare(strict_types=1);

namespace Murergrej\Hyva\Helper;

use Magento\Swatches\Helper\Data as SwatchesHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;
use Magento\Swatches\Model\ResourceModel\Swatch as SwatchResource;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Magento\Swatches\Model\SwatchAttributeType;
use Magento\Swatches\Model\Swatch;

class Data extends SwatchesHelper
{
    /**
     * @var array
     */
    private $swatchesCache = [];

    /**
     * @var SwatchResource
     */
    private $swatchResource;

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param SwatchCollectionFactory $swatchCollectionFactory
     * @param UrlBuilder $urlBuilder
     * @param Json|null $serializer
     * @param SwatchAttributesProvider|null $swatchAttributesProvider
     * @param SwatchAttributeType|null $swatchTypeChecker
     * @param SwatchResource $swatchResource
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        SwatchCollectionFactory $swatchCollectionFactory,
        UrlBuilder $urlBuilder,
        Json $serializer = null,
        SwatchAttributesProvider $swatchAttributesProvider = null,
        SwatchAttributeType $swatchTypeChecker = null,
        SwatchResource $swatchResource
    ) {
        parent::__construct(
            $productCollectionFactory,
            $productRepository,
            $storeManager,
            $swatchCollectionFactory,
            $urlBuilder,
            $serializer,
            $swatchAttributesProvider,
            $swatchTypeChecker,
            $swatchResource
        );

        $this->swatchResource = $swatchResource; // Assign the resource
    }

    /**
     * @param array $optionIds
     * @return array
     */
    public function getSwatchesByOptionsId(array $optionIds)
    {
        $swatches = $this->getCachedSwatches($optionIds);

        if (count($swatches) !== count($optionIds)) {
            $swatchOptionIds = array_diff($optionIds, array_keys($swatches));
            $swatchCollection = $this->swatchCollectionFactory->create();
            $swatchCollection->addFilterByOptionsIds($swatchOptionIds);

            // Join with the eav_attribute_option table to get the code
            $eavAttributeOptionTable = $this->swatchResource->getTable('eav_attribute_option');
            $swatchCollection->getSelect()->join(
                ['option_table' => $eavAttributeOptionTable],
                'option_table.option_id = main_table.option_id',
                ['code']
            );

            $swatches = [];
            $fallbackValues = [];
            $currentStoreId = $this->storeManager->getStore()->getId();
            foreach ($swatchCollection->getData() as $item) {
                if ($item['type'] != Swatch::SWATCH_TYPE_TEXTUAL) {
                    $swatches[$item['option_id']] = $item;
                } elseif ($item['store_id'] == $currentStoreId && $item['value'] != '') {
                    $fallbackValues[$item['option_id']][$currentStoreId] = $item;
                } elseif ($item['store_id'] == self::DEFAULT_STORE_ID) {
                    $fallbackValues[$item['option_id']][self::DEFAULT_STORE_ID] = $item;
                }
            }

            if (!empty($fallbackValues)) {
                $swatches = $this->addFallbackOptions($fallbackValues, $swatches);
            }
            $this->setCachedSwatches($swatchOptionIds, $swatches);
        }

        return array_filter($this->getCachedSwatches($optionIds));
    }

    /**
     * @param array $optionIds
     * @return array
     */
    private function getCachedSwatches(array $optionIds)
    {
        return array_intersect_key($this->swatchesCache, array_flip($optionIds));
    }

    /**
     * @param array $optionIds
     * @param array $swatches
     * @return void
     */
    private function setCachedSwatches(array $optionIds, array $swatches)
    {
        foreach ($optionIds as $optionId) {
            if (isset($swatches[$optionId])) {
                $this->swatchesCache[$optionId] = $swatches[$optionId];
            }
        }
    }
}
