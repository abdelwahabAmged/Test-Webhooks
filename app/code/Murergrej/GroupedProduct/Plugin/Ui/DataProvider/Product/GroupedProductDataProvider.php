<?php

namespace Murergrej\GroupedProduct\Plugin\Ui\DataProvider\Product;

use Magento\GroupedProduct\Ui\DataProvider\Product\GroupedProductDataProvider as Subject;

class GroupedProductDataProvider
{
    public function afterGetData(Subject $subject, $result)
    {
        foreach ($result['items'] as &$item) {
            $item['price_value'] = (float)$item['price'];
        }
        return $result;
    }
}
