<?php

namespace Murergrej\GroupedProduct\Plugin\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price as Subject;

class Price
{
    /**
     * Retrieve product final price
     *
     * @param Subject $subject
     * @param callable $proceed
     * @param float|null $qty
     * @param Product $product
     * @return float
     */
    public function aroundGetFinalPrice(Subject $subject, $proceed, $qty, $product)
    {
        if ($price = $this->getCustomGroupedPrice($product)) {
            return $price;
        }
        return $proceed($qty, $product);
    }

    public function aroundGetTierPrice(Subject $subject, $proceed, $qty, $product)
    {
        if ($this->isCustomGroupedPrice($product)) {
            return [];
        }
        return $proceed($qty, $product);
    }

    public function aroundGetTierPrices(Subject $subject, $proceed, $product)
    {
        if ($this->isCustomGroupedPrice($product)) {
            return [];
        }
        return $proceed($product);
    }

    /**
     * @param Product $product
     * @return bool
     */
    protected function isCustomGroupedPrice($product)
    {
        return (bool)$this->getCustomGroupedPrice($product);
    }

    /**
     * @param Product $product
     * @return bool
     */
    protected function getCustomGroupedPrice($product)
    {
        if (!$product->hasCustomOptions()) {
            return false;
        }
        $option = $product->getCustomOption('grouped_link_price');
        if (!$option) {
            return false;
        }
        return $option->getValue();
    }
}
