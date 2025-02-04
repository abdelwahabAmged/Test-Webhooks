<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Hyva
 * @author      Jorgena Shinjatari info@scandiweb.com
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
*/

namespace Murergrej\Hyva\Block\Adminhtml\Attribute\Edit\Options;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options as CoreOptions;
use Magento\Framework\DataObject;

class Options extends CoreOptions
{
    /**
     * Prepare option values of user defined attribute
     *
     * @param array|\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option $option
     * @param string $inputType
     * @param array $defaultValues
     * @return array
     */
    protected function _prepareUserDefinedAttributeOptionValues($option, $inputType, $defaultValues)
    {
        $optionId = $option->getId();

        $value['checked'] = in_array($optionId, $defaultValues) ? 'checked="checked"' : '';
        $value['intype'] = $inputType;
        $value['id'] = $optionId;
        $value['sort_order'] = $option->getSortOrder();

        // Adding custom attributes
        $value['code'] = $this->getData('code');

        foreach ($this->getStores() as $store) {
            $storeId = $store->getId();
            $storeValues = $this->getStoreOptionValues($storeId);
            $value['store' . $storeId] = isset(
                $storeValues[$optionId]
            ) ? $this->escapeHtml(
                $storeValues[$optionId]
            ) : '';
        }

        return [$value];
    }
}
