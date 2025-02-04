<?php

namespace Murergrej\InvoiceEmail\Db\Sql;

class CoalesceExpression extends \Magento\Framework\DB\Sql\Expression
{
    public function __construct(array $columns, \Magento\Framework\App\ResourceConnection $resource)
    {
        $connection = $resource->getConnection();

        $cols = [];
        $lastKey = $this->arrayLastKey($columns);
        foreach ($columns as $key => $column) {
            if ($key == $lastKey) {
                $cols[] = $connection->quoteIdentifier($column);
            } else {
                $cols[] = 'NULLIF(' . $connection->quoteIdentifier($column) . ', \'\')';
            }
        }
        $expr = 'COALESCE(' . implode(', ', $cols) . ')';
        parent::__construct($expr);
    }

    protected function arrayLastKey($array)
    {
        end($array);
        return key($array);
    }
}
