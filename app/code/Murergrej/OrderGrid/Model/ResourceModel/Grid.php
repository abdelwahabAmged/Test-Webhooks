<?php

namespace Murergrej\OrderGrid\Model\ResourceModel;

class Grid extends \Magento\Sales\Model\ResourceModel\Grid
{
    public function getTable($tableName)
    {
        if ($tableName instanceof \Zend_Db_Expr) {
            return $tableName;
        }
        return parent::getTable($tableName);
    }
}
