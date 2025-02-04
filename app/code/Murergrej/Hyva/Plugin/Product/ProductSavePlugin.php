<?php
namespace Murergrej\Hyva\Plugin\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Validation\ValidationException;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

class ProductSavePlugin
{
    protected $productResource;

    public function __construct(ProductResource $productResource)
    {
        $this->productResource = $productResource;
    }

    public function beforeSave(Product $product)
    {
        // Get the delivery time and default delivery time
        $deliveryTime = $product->getDeliveryTime();
        $defaultDeliveryTime = $product->getDefaultDeliveryTime();

        // Get attribute labels
        $deliveryTimeLabel = $this->productResource->getAttribute('delivery_time')->getStoreLabel();
        $defaultDeliveryTimeLabel = $this->productResource->getAttribute('default_delivery_time')->getStoreLabel();

        // Validate that both fields are not filled at the same time
        if ($deliveryTime && $defaultDeliveryTime) {
            throw new ValidationException(__(
                'Please fill either ' . $deliveryTimeLabel . ' or ' . $defaultDeliveryTimeLabel . ', but not both.'
            ));
        }

        // Validate the format of default delivery time
        if ($defaultDeliveryTime && !preg_match('/^\d+$|^\d+-\d+$/', $defaultDeliveryTime)) {
            throw new ValidationException(__(
                'The ' . $defaultDeliveryTimeLabel . ' must be in the format "digit-digit" or just a digit.'
            ));
        }
    }
}
