<?php

namespace PWA\Banner\Ui\Component\Listing\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;

/**
 * Class SearchFilter
 */
class SearchFilter implements FilterApplierInterface
{
    /**
     * @var string[]
     */
    protected $columns;

    /**
     * SearchFilter constructor.
     * @param array $columns
     */
    public function __construct(array $columns = [])
    {
        $this->columns = $columns;
    }

    /**
     * Apply fulltext filters
     *
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     */
    public function apply(Collection $collection, Filter $filter)
    {
        if (!$collection instanceof AbstractDb) {
            throw new \InvalidArgumentException('Database collection required.');
        }

        if (empty($this->columns)) {
            return;
        }

        $value = sprintf('%%%s%%', $filter->getValue());

        $i = 0;
        $count = count($this->columns);

        foreach ($this->columns as $column) {
            $i++;
            if ($count > 1) {
                if ($i == 1) {
                    $collection->getSelect()->where('(' . $column . ' LIKE ?', (string)$value);
                } else if ($i == $count) {
                    $collection->getSelect()->orWhere($column . ' LIKE ?)', (string)$value);
                } else {
                    $collection->getSelect()->orWhere($column . ' LIKE ?', (string)$value);
                }
            } else {
                $collection->getSelect()->where($column . ' LIKE ?', (string)$value);
            }
        }
    }
}
