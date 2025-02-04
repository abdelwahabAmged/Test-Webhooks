<?php
/**
 * @category Murergrej
 * @package Murergrej_Hyva
 * @author Jorgena Shinjatari info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
*/

declare(strict_types=1);

namespace Murergrej\Hyva\Block\Adminhtml\Attribute\Edit\Options;

use \Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\Visual;

class CustomVisual extends Visual
{
    /**
     * @return false|string
     */
    public function getJsonConfig()
    {
        $values = [];

        // Retrieve code values
        $codeValues = $this->getCodeValues();

        // Get existing option values
        foreach ($this->getOptionValues() as $value) {
            $optionId = $value->getId();
            $optionData = $value->getData();
            $optionData['code'] = $codeValues[$optionId]['code'];

            $values[] = $optionData;
        }

        $data = [
            'attributesData' => $values,
            'uploadActionUrl' => $this->getUrl('swatches/iframe/show'),
            'isSortable' => (int)(!$this->getReadOnly() && !$this->canManageOptionDefaultOnly()),
            'isReadOnly' => (int)$this->getReadOnly()
        ];

        return json_encode($data);
    }

    /**
     * @return array
     */
    protected function getCodeValues()
    {
        $values = [];

        $valuesCollection = $this->_attrOptionCollectionFactory->create()->setAttributeFilter(
            $this->getAttributeObject()->getId()
        )->load();

        foreach ($valuesCollection as $item) {
            $optionId = $item->getId();
            $values[$optionId] = [
                'code' => $item->getCode()
            ];
        }

        return $values;
    }
}
