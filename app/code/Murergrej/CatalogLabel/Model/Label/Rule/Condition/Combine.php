<?php

namespace Murergrej\CatalogLabel\Model\Label\Rule\Condition;

use Mirasvit\CatalogLabel\Model\Label\Rule\Condition\Combine as MirasvitCombine;

class Combine extends MirasvitCombine
{
    /**
     * @var array
     */
    protected $_groups = [
        'base' => [
            'name',
            'attribute_set_id',
            'sku',
            'category_ids',
            'url_key',
            'visibility',
            'status',
            'default_category_id',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'price',
            'special_price',
            'special_price_from_date',
            'special_price_to_date',
            'tax_class_id',
            'short_description',
            'full_description',
        ],
        'extra' => [
            'created_at',
            'updated_at',
            'qty',
            'final_price',
            'price_diff',
            'percent_discount',
            'set_as_new',
            'is_salable',
            'product_has_tier_prices',
            'product_has_supplier_orders'
        ],
    ];
}
