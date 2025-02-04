<?php
/**
 * @category Murergrej
 * @package Murergrej_Hyva
 * @author Jorgena Shinjatari info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
*/

declare(strict_types=1);

namespace Murergrej\Hyva\Block\Product\Renderer;

use Magento\Swatches\Block\Product\Renderer\Configurable as SwatchesConfigurable;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Swatches\Model\Swatch;

class CustomConfigurable extends SwatchesConfigurable implements IdentityInterface
{
    /**
     * Get Swatch config data
     *
     * @return string
     */
    public function getJsonSwatchConfig()
    {
        $attributesData = $this->getSwatchAttributesData();
        $allOptionIds = $this->getConfigurableOptionsIds($attributesData);
        $swatchesData = $this->swatchHelper->getSwatchesByOptionsId($allOptionIds);
        $config = [];
        foreach ($attributesData as $attributeId => $attributeDataArray) {
            if (isset($attributeDataArray['options'])) {
                $config[$attributeId] = $this->addSwatchDataForAttribute(
                    $attributeDataArray['options'],
                    $swatchesData,
                    $attributeDataArray
                );
            }
            if (isset($attributeDataArray['additional_data'])) {
                $config[$attributeId]['additional_data'] = $attributeDataArray['additional_data'];
            }
        }

        return $this->jsonEncoder->encode($config);
    }

    /**
     * Retrieve Swatch data for config
     *
     * @param array $swatchDataArray
     * @return array
     */
    protected function extractNecessarySwatchData(array $swatchDataArray)
    {
        $result['type'] = $swatchDataArray['type'];
        $result['code'] = $swatchDataArray['code'];

        if ($result['type'] == Swatch::SWATCH_TYPE_VISUAL_IMAGE && !empty($swatchDataArray['value'])) {
            $result['value'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_IMAGE_NAME,
                $swatchDataArray['value']
            );
            $result['thumb'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_THUMBNAIL_NAME,
                $swatchDataArray['value']
            );
        } else {
            $result['value'] = $swatchDataArray['value'];
        }

        return $result;
    }
}
