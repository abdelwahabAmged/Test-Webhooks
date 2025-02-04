<?php

namespace Murergrej\GroupedProduct\Plugin\Model\Product\Initialization\Helper;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped as TypeGrouped;

class ProductLinks
{
    /**
     * String name for link type
     */
    const TYPE_NAME = 'associated';

    /**
     * Initialize grouped product links
     *
     * @param \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param array $links
     *
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function beforeInitializeLinks(
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $subject,
        \Magento\Catalog\Model\Product $product,
        array $links
    ) {
        if ($product->getTypeId() !== TypeGrouped::TYPE_CODE || $product->getGroupedReadonly()) {
            return null;
        }

        if (isset($links[self::TYPE_NAME])) {
            foreach ($links[self::TYPE_NAME] as &$link) {
                if (isset($link['price'])) {
                    $link['custom_attributes']['link_price'] = [
                        'attribute_code' => 'link_price',
                        'value' => $link['price']
                    ];
                }
            }

            return [$product, $links];
        } else if ($links = $product->getGroupedLinkData()) {
            return null;
        } else {
            return null;
        }
    }
}
