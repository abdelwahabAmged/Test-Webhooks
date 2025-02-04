<?php

namespace Murergrej\GroupedProduct\Plugin\Pricing\Price;

use Magento\Catalog\Pricing\Price\TierPrice as Subject;

class TierPrice
{
    public function aroundGetValue(Subject $subject, $proceed)
    {
        if ($this->isCustomGroupedPrice($subject->getProduct())) {
            return false;
        }
        return $proceed();
    }

    public function aroundGetTierPriceList(Subject $subject, $proceed)
    {
        if ($this->isCustomGroupedPrice($subject->getProduct())) {
            return [];
        }
        return $proceed();
    }

    /**
     * @param $product
     * @return bool
     */
    protected function isCustomGroupedPrice($product)
    {
        return $product
            && $product instanceof \Magento\Catalog\Model\Product
            && $product->getData('link_price');
    }
}
