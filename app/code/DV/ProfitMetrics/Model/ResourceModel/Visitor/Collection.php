<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Model\ResourceModel\Visitor;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'dv_profitmetrics_visitor_collection';
    protected $_eventObject = 'dv_profitmetrics_visitor_collection';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \DV\ProfitMetrics\Model\Visitor::class,
            \DV\ProfitMetrics\Model\ResourceModel\Visitor::class
        );
    }
}
