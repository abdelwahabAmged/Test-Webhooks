<?php

/**
 * Class to Override the getJsonCategoryData method to be compatible with Hello Retail
 * @category  Scandiweb
 * @package   Scandiweb_Mageworx
 * @author    Ingy El-Khateeb
 */
namespace Scandiweb\Mageworx\Helper\Json;

use MageWorx\SeoMarkup\Helper\Json\Category as MageWorxCategory;

class Category extends MageWorxCategory
{
    /**
     * Override the getJsonCategoryData method
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return array|bool
     */
    protected function getJsonCategoryData($category)
    {
        // Custom logic here
        $productCollection = $this->getProductCollection();

        $data = [];

        if ($productCollection) {
            $data['@context']                      = 'http://schema.org';
            $data['@type']                         = 'WebPage';
            $data['url']                           = $this->urlBuilder->getCurrentUrl();
            $data['mainEntity']                    = [];
            $data['mainEntity']['@context']        = 'http://schema.org';
            $data['mainEntity']['@type']           = 'CustomOfferCatalog'; // Example of a modification
            $data['mainEntity']['name']            = $category->getName();
            $data['mainEntity']['url']             = $this->urlBuilder->getCurrentUrl();
            $data['mainEntity']['numberOfItems']   = count($productCollection['collection']->getItems());
            $data['mainEntity']['itemListElement'] = [];

            if ($this->helperCategory->isUseOfferForCategoryProducts()) {
                foreach ($productCollection as $product) {
                    $data['mainEntity']['itemListElement'][] = $this->getProductData($product);
                }
            }
        }

        return $data;
    }
}
