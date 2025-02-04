<?php

namespace Murergrej\GroupedProduct\Plugin\Model\Product\Link\ProductEntity;

use Magento\Catalog\Model\ProductLink\Converter\ConverterInterface as Subject;

class Converter
{
    /**
     * @param Subject $subject
     * @param array $result
     * @param \Magento\Catalog\Model\Product $product
     */
    public function afterConvert(Subject $subject, $result, $product)
    {
        $result['custom_attributes'][] = [
            'attribute_code' => 'link_price',
            'value' => $product->getData('link_price')
        ];
        return $result;
    }
}
