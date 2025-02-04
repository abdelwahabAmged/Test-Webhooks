<?php
/**
 * @category  Murergrej
 * @package   Murergrej_Hyva
 * @author    Jorgena Shinjatari info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 * @license   https://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Murergrej\Hyva\Block\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Block\Product\View\Attributes as SourceAttributes;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Cms\Model\Template\FilterProvider;

class Attributes extends SourceAttributes
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param PriceCurrencyInterface $priceCurrency
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $registry, $priceCurrency, $data);
    }

    /**
     * @param array $excludeAttr
     * @return array
     */
    public function getAdditionalData(array $excludeAttr = [])
    {
        $data = [];
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        $sortedAttributes = [];

        foreach ($attributes as $attribute) {
            if ($this->isVisibleOnFrontend($attribute, $excludeAttr) && ($attribute->getData('used_in_product_info_tabs') === '1')) {
                $value = $attribute->getFrontend()->getValue($product);

                if ($value instanceof Phrase) {
                    $value = (string)$value;
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                // Use the filter provider to process HTML content, if applicable
                if (is_string($value) && strlen(trim($value))) {
                    // Replace media placeholders in the value
                    $value = $this->filterProvider->getPageFilter()->filter($value);

                    $sortedAttributes[] = [
                        'label' => $attribute->getStoreLabel(),
                        'value' => html_entity_decode($value),
                        'code' => $attribute->getAttributeCode(),
                        'sort_order' => $attribute->getData('sort_order_of_product_information_tabs'),
                        'attribute_id' => $attribute->getId(),
                    ];
                }
            }
        }

        // Sort the attributes first by sort order, then by attribute ID
        usort($sortedAttributes, function ($a, $b) {
            // Place attributes without a sort order last
            if (!isset($a['sort_order'])) return 1;
            if (!isset($b['sort_order'])) return -1;
            return $a['sort_order'] <=> $b['sort_order'] ?: $a['attribute_id'] <=> $b['attribute_id'];
        });

        // Build the final data array from the sorted attributes
        foreach ($sortedAttributes as $attr) {
            $data[$attr['code']] = [
                'label' => $attr['label'],
                'value' => $attr['value'],
                'code' => $attr['code'],
            ];
        }

        return $data;
    }
}
