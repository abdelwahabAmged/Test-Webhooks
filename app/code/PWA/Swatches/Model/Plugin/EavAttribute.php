<?php

namespace PWA\Swatches\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Swatches\Model\ResourceModel\Swatch as SwatchResource;
use Magento\Swatches\Model\Swatch;

class EavAttribute extends \Magento\Swatches\Model\Plugin\EavAttribute
{
    /**
     * Prefix added to option value added through API
     */
    private const API_OPTION_PREFIX = 'id_';

    public function __construct(SwatchResource\CollectionFactory $collectionFactory, \Magento\Swatches\Model\SwatchFactory $swatchFactory, \Magento\Swatches\Helper\Data $swatchHelper, Json $serializer = null, SwatchResource $swatchResource = null)
    {
        parent::__construct($collectionFactory, $swatchFactory, $swatchHelper, $serializer, $swatchResource);
    }

    protected function processVisualSwatch($attribute)
    {
        $customArray = $attribute->getData('swatch/custom');
        if (!isset($customArray) || !is_array($customArray)) {
            parent::processVisualSwatch($attribute);
        }
        $swatchArray = $attribute->getData('swatch/value');
        if (isset($swatchArray) && is_array($swatchArray)) {
            foreach ($swatchArray as $optionId => $value) {
                $optionId = $this->getAttributeOptionId($optionId);
                $isOptionForDelete = $this->isOptionForDelete($attribute, $optionId);
                if ($optionId === null || $isOptionForDelete) {
                    //option was deleted by button with basket
                    continue;
                }
                $swatch = $this->loadSwatchIfExists($optionId, self::DEFAULT_STORE_ID);

                $swatchType = $this->determineSwatchType($value);

                $this->saveSwatchDataCustom($swatch, $optionId, self::DEFAULT_STORE_ID, $swatchType, $value, $customArray[$optionId] ?? []);
                $this->isSwatchExists = null;
            }
        }
    }

    /**
     * Save operation
     *
     * @param Swatch $swatch
     * @param integer $optionId
     * @param integer $storeId
     * @param integer $type
     * @param string $value
     * @param array $custom
     * @return void
     */
    protected function saveSwatchDataCustom($swatch, $optionId, $storeId, $type, $value, $custom = [])
    {
        if (!$this->isSwatchExists) {
            $swatch->setData('option_id', $optionId);
            $swatch->setData('store_id', $storeId);
        }
        $swatch->setData('type', $type);
        $swatch->setData('value', $value);
        $swatch->setData('image', $custom['image'] ?? null);
        $swatch->setData('image_mobile', $custom['image_mobile'] ?? null);
        $swatch->setData('number', $custom['number'] ?? null);

        $swatch->save();
    }

    /**
     * Get the visual swatch type based on its value
     *
     * @param string $value
     * @return int
     */
    private function determineSwatchType($value)
    {
        $swatchType = Swatch::SWATCH_TYPE_EMPTY;
        if (!empty($value) && $value[0] == '#') {
            $swatchType = Swatch::SWATCH_TYPE_VISUAL_COLOR;
        } elseif (!empty($value) && $value[0] == '/') {
            $swatchType = Swatch::SWATCH_TYPE_VISUAL_IMAGE;
        }
        return $swatchType;
    }
}
