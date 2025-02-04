<?php

namespace Murergrej\OrderGrid\Db\Sql;

class AggregateOrderItemExpression extends \Magento\Framework\DB\Sql\Expression
{
    /**
     * Cached resources singleton
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    public function __construct(array $columns, \Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
        $table = $this->resource->getTableName('sales_order_item');
        $connection = $this->resource->getConnection();
        $cols = [
            'order_id'
        ];
        foreach ($columns as $column) {
            $cols[$column] = new \Zend_Db_Expr('GROUP_CONCAT(' . $connection->quoteIdentifier($column) . ' SEPARATOR ", ")');
        }
        $select = $this->resource->getConnection()->select()
            ->from($table, $cols)
            ->where('parent_item_id IS NULL')
            ->group('order_id');
        parent::__construct('(' . $select . ')');
    }
}
