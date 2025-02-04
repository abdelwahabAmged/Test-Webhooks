<?php

namespace PWA\Swatches\Block\Adminhtml\Attribute\Edit\Options;

class Visual extends \Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\Visual
{

    /**
     * Create store values
     *
     * Method not intended to escape HTML entities
     * Escaping will be applied in template files
     *
     * @param integer $storeId
     * @param integer $optionId
     * @return array
     */
    protected function createStoreValues($storeId, $optionId)
    {
        $value = [];
        $value['store' . $storeId] = '';
        $value['defaultswatch' . $storeId] = '';
        $value['swatch' . $storeId] = '';
        $storeValues = $this->getStoreOptionValues($storeId);
        $swatchStoreValue = null;

        if (isset($storeValues['swatch'])) {
            $swatchStoreValue = $storeValues['swatch'];
        }

        if (isset($storeValues[$optionId])) {
            $value['store' . $storeId] = $storeValues[$optionId];
        }

        if (isset($swatchStoreValue[$optionId])) {
            $value['defaultswatch' . $storeId] = $swatchStoreValue[$optionId];
        }

        $swatchStoreValue = $this->reformatSwatchLabels($swatchStoreValue);
        if (isset($swatchStoreValue[$optionId])) {
            $value['swatch' . $storeId] = $swatchStoreValue[$optionId];
        }

        if (isset($storeValues['custom'][$optionId])) {
            $custom = $storeValues['custom'][$optionId];
            if (isset($custom['image'])) {
                $value['defaultimage'] = $custom['image'];
                $value['image'] = $this->reformatSwatchLabels([$custom['image']])[0] ?? null;
            }

            if (isset($custom['image_mobile'])) {
                $value['defaultimage_mobile'] = $custom['image_mobile'];
                $value['image_mobile'] = $this->reformatSwatchLabels([$custom['image_mobile']])[0] ?? null;
            }

            if (isset($custom['number'])) {
                $value['number'] = $custom['number'];
            }
        }

        return $value;
    }

    /**
     * Retrieve attribute option values for given store id
     *
     * @param int $storeId
     * @return array
     */
    public function getStoreOptionValues($storeId)
    {
        $values = $this->getData('store_option_values_' . $storeId);
        if ($values === null) {
            $values = [];
            $valuesCollection = $this->_attrOptionCollectionFactory->create();
            $valuesCollection->setAttributeFilter(
                $this->getAttributeObject()->getId()
            );
            $this->addCollectionStoreFilter($valuesCollection, $storeId);
            $valuesCollection->getSelect()->joinLeft(
                ['swatch_table' => $valuesCollection->getTable('eav_attribute_option_swatch')],
                'swatch_table.option_id = main_table.option_id AND swatch_table.store_id = '.$storeId,
                [
                    'swatch_table.value AS label', 'swatch_table.image AS image', 'swatch_table.image_mobile AS image_mobile', 'swatch_table.number AS number'
                ]
            );
            $valuesCollection->load();
            foreach ($valuesCollection as $item) {
                $values[$item->getId()] = $item->getValue();
                $values['swatch'][$item->getId()] = $item->getLabel();
                $values['custom'][$item->getId()] = [
                    'image' => $item->getImage(),
                    'image_mobile' => $item->getImageMobile(),
                    'number' => $item->getNumber()
                ];
            }
            $this->setData('store_option_values_' . $storeId, $values);
        }
        return $values;
    }

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $valuesCollection
     * @param int $storeId
     * @return void
     */
    private function addCollectionStoreFilter($valuesCollection, $storeId)
    {
        $joinCondition = $valuesCollection->getConnection()->quoteInto(
            'tsv.option_id = main_table.option_id AND tsv.store_id = ?',
            $storeId
        );

        $select = $valuesCollection->getSelect();
        $select->joinLeft(
            ['tsv' => $valuesCollection->getTable('eav_attribute_option_value')],
            $joinCondition,
            'value'
        );
        if (\Magento\Store\Model\Store::DEFAULT_STORE_ID == $storeId) {
            $select->where(
                'tsv.store_id = ?',
                $storeId
            );
        }
        $valuesCollection->setOrder('value', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }
}
