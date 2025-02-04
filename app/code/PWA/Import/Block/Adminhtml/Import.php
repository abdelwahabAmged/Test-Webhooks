<?php

namespace PWA\Import\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

class Import extends Template
{
    public function getActions()
    {
        return [
            'productsdelta' => 'Products Delta',
            'cms' => 'CMS',
            'attributes' => 'Product Attributes',
            'categories' => 'Categories',
            'products' => 'Products',
            'reindex' => 'Reindex Database',
            'import' => 'Full Reimport'
        ];
    }
}
