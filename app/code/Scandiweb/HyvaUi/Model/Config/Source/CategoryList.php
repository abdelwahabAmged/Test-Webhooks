<?php

/**
 * @category    Scandiweb
 * @author      Amr Osama <amr.osama@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Scandiweb\HyvaUi\Model\Config\Source;

use Magento\Catalog\Helper\Category;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Data\OptionSourceInterface;

class CategoryList implements OptionSourceInterface
{
    protected Category $_categoryHelper;

    /**
     * @param Category $catalogCategory
     */
    public function __construct(Category $catalogCategory)
    {
        $this->_categoryHelper = $catalogCategory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $categories = $this->_categoryHelper->getStoreCategories(true, false, true);

        $categoryList = [];
        foreach ($categories as $category) {
            $categoryList[$category->getEntityId()] = __($category->getName());
        }

        return $categoryList;
    }
}
