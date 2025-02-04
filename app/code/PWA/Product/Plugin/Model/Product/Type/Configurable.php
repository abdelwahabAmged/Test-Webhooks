<?php

namespace PWA\Product\Plugin\Model\Product\Type;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as Subject;

class Configurable
{
    protected $attributeCodes = [
        'icon_top_label_1',
        'icon_top_label_2',
        'icon_bottom_label_1',
        'icon_bottom_label_2',
        'icon_bottom_label_3'
    ];

    protected $attributeIds = null;

    protected $eavAttribute;

    public function __construct(\Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute)
    {
        $this->eavAttribute = $eavAttribute;
    }

    public function beforeGetUsedProducts(Subject $subject, $product, $requiredAttributeIds = null)
    {
        if (is_null($requiredAttributeIds)) {
            $requiredAttributeIds = [];
        }
        $requiredAttributeIds = array_merge($requiredAttributeIds, $this->getAttributeIds());
        return [$product, $requiredAttributeIds];
    }

    public function getAttributeIds()
    {
        if ($this->attributeIds !== null) {
            return $this->attributeIds;
        }
        $this->attributeIds = [];

        foreach ($this->attributeCodes as $code) {
            $id = $this->eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, $code);
            if ($id) {
                $this->attributeIds[] = $id;
            }
        }

        return $this->attributeIds;
    }
}
