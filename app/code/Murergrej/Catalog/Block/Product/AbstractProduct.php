<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Catalog
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Catalog\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct as SourceAbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Config;

/**
 * Class AbstractProduct
 */
class AbstractProduct extends SourceAbstractProduct
{
    /**
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param Config $catalogConfig
     * @param CollectionFactory $attributeCollectionFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        protected AttributeSetRepositoryInterface $attributeSetRepository,
        protected Config $catalogConfig,
        protected CollectionFactory $attributeCollectionFactory,
        Context $context, array $data = []
    ) {
      parent::__construct($context, $data);
    }

    /**
     * @param Product $product
     * @param $groupName
     * @return array
     */
    public function getAttributesByGroup(Product $product, $groupName = "Produkt attributter"): array
    {
        $attributeSetId = $product->getAttributeSetId();
        $groupId = $this->catalogConfig->getAttributeGroupId($attributeSetId, $groupName);

        $attributeCollection = $this->attributeCollectionFactory->create()
            ->setAttributeGroupFilter($groupId)
            ->setAttributeSetFilter($attributeSetId);

        $attributes = [];
        foreach ($attributeCollection as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if ($attributeCode === 'weight') {
                $value = (float) $product->getData($attributeCode) . ' kgs';
            } else {
                $value = $attribute->getSource()->getOptionText($product->getData($attributeCode));
            }

            // Add only attributes with a value
            if ($value) {
                $attributes[$attributeCode] = [
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $value,
                ];
            }
        }

        return $attributes;
    }
}
