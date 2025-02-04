<?php

namespace Murergrej\GroupedProduct\Model\Product\Type;

class Grouped extends \Magento\GroupedProduct\Model\Product\Type\Grouped
{
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);
        if (is_array($result)) {
            /** @var \Magento\Catalog\Model\Product $item */
            foreach ($result as $item) {
                if ($item->getData('link_price')) {
                    $item->addCustomOption('grouped_link_price', $item->getData('link_price'));
                }
            }
            if (($qty = $buyRequest->getQty()) > 1) {
                foreach ($result as $item) {
                    $item
                        ->setQty($item->getQty() * $qty)
                        ->setCartQty($item->getCartQty() * $qty);
                }
            }
        }

        return $result;
    }
}
