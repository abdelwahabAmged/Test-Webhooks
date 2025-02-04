<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Model\ResourceModel;

class Visitor extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('dv_profitmetrics_visitor', 'entity_id');
    }
}
