<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Hyva
 * @author      Jorgena Shinjatari info@scandiweb.com
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
*/

declare(strict_types=1);

namespace Murergrej\Hyva\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Attribute as OriginalAttribute;

class Attribute extends OriginalAttribute
{
    /**
     * @param AbstractModel $object
     * @param int $optionId
     * @param array $option
     * @return false|int
     */
    protected function _updateAttributeOption($object, $optionId, $option)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('eav_attribute_option');
        // ignore strings that start with a number
        $intOptionId = is_numeric($optionId) ? (int)$optionId : 0;

        if (!empty($option['delete'][$optionId])) {
            if ($intOptionId) {
                $connection->delete($table, ['option_id = ?' => $intOptionId]);
                $this->clearSelectedOptionInEntities($object, $intOptionId);
            }
            return false;
        }

        $sortOrder  = empty($option['order'][$optionId]) ? 0 : $option['order'][$optionId];
        $optionCode = empty($option['code'][$optionId]) ? NULL : $option['code'][$optionId];

        if (!$intOptionId) {
            $data = [
                'attribute_id' => $object->getId(),
                'sort_order' => $sortOrder,
                'code'  => $optionCode
            ];
            $connection->insert($table, $data);
            $intOptionId = $connection->lastInsertId($table);
        } else {
            $data = [
                'sort_order' => $sortOrder,
                'code'  => $optionCode
            ];
            $where = ['option_id = ?' => $intOptionId];
            $connection->update($table, $data, $where);
        }

        return $intOptionId;
    }
}
