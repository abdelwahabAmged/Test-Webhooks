<?php

declare(strict_types=1);

namespace Murergrej\CatalogLabel\Model\Label\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\CatalogLabel\Model\Label\Rule\Condition\Product\AbstractProductCondition;
use Magento\Rule\Model\Condition\AbstractCondition;

class ProductHasSupplierOrders extends AbstractProductCondition
{
    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'product_has_supplier_orders';
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return (string)__('Product has supplier orders enabled');
    }

    /**
     * @return array[]|null
     */
    public function getValueOptions(): ?array
    {
        return [
            ['value' => 0, 'label' => (string)__('No')],
            ['value' => 1, 'label' => (string)__('Yes')],
        ];
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return self::TYPE_SELECT;
    }

    /**
     * @return string
     */
    public function getValueElementType(): string
    {
        return self::TYPE_SELECT;
    }

    /**
     * @param AbstractModel $object
     * @param AbstractCondition $validator
     * @return bool
     */
    public function validate(AbstractModel $object, AbstractCondition $validator): bool
    {
        // Check if the product has the 'supplier_orders' attribute enabled
        return $validator->validateAttribute((bool)$object->getData('supplier_orders'));
    }

    /**
     * @return array
     */
    public function getExtraAttributesToSelect(): array
    {
        return ['supplier_orders'];
    }
}
