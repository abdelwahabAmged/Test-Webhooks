<?php

declare(strict_types=1);

namespace Murergrej\CatalogLabel\Model\Label\Rule\Condition;

use Magento\Backend\Model\Url;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Config;
use Magento\Framework\Locale\FormatInterface;
use Magento\Rule\Model\Condition\Context;
use Mirasvit\CatalogLabel\Model\Label\Rule\Condition\Product\AbstractProductCondition;
use Mirasvit\CatalogLabel\Model\Label\Rule\Condition\Product as MirasvitProduct;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends MirasvitProduct
{
    /**
     * @var AbstractProductCondition[]
     */
    private array $extraRulesPool;

    /**
     * @param ProductFactory $productFactory
     * @param Url $backendUrlManager
     * @param FormatInterface $localeFormat
     * @param Config $eavConfig
     * @param Context $context
     * @param array $extraRulesPool
     * @param array $data
     */
    public function __construct(
        ProductFactory $productFactory,
        Url $backendUrlManager,
        FormatInterface $localeFormat,
        Config $eavConfig,
        Context $context,
        array $extraRulesPool,
        array $data = []
    ) {
        $this->extraRulesPool    = $extraRulesPool;

        parent::__construct(
            $productFactory,
            $backendUrlManager,
            $localeFormat,
            $eavConfig,
            $context,
            $extraRulesPool,
            $data
        );
    }

    /**
     * @param Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes(Collection $productCollection): self
    {
        $attribute = $this->getAttribute();

        if (!in_array($attribute, ['product_has_tier_prices', 'product_has_supplier_orders', 'category_ids', 'qty', 'final_price', 'price_diff', 'percent_discount', 'set_as_new', 'stock_status', 'is_salable'])) {
            if ($this->getAttributeObject()->isScopeGlobal()) {
                $attributes = $this->getRule()->getCollectedAttributes();
                $attributes[$attribute] = true;

                $this->getRule()->setCollectedAttributes($attributes);
                $productCollection->addAttributeToSelect($attribute, 'left');
            } else {
                $this->_entityAttributeValues = $productCollection->getAllAttributeValues($attribute);
            }
        } elseif (isset($this->extraRulesPool[$attribute])) {
            foreach ($this->extraRulesPool[$attribute]->getExtraAttributesToSelect() as $attrCode) {
                $productCollection->addAttributeToSelect($attrCode, 'left');
            }
        }

        return $this;
    }
}
