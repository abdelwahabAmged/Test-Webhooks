<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Hyva
 * @author      Jorgena Shinjatari info@scandiweb.com
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
*/

declare(strict_types=1);

namespace Murergrej\Hyva\Block;

use Magento\Framework\View\Element\Template;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;

class GetProductStockStatus extends Template
{
    /**
     * @param Context $context
     * @param StockRegistryInterface $stockRegistry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context, $data);
    }

    /**
     * @param $product
     * @return false[]
     */
    public function checkStockStatus($product)
    {
        $productId = $product->getId();
        $stockItem = $this->stockRegistry->getStockItem($productId);

        $productIsInStock = $stockItem->getIsInStock();
        $permanentlyOos = $product->getData('permanently_oos');

        // Initialize the response array
        $stockStatus = [
            'permanently_out_of_stock'  => false,
            'temporary_out_of_stock'    => false,
        ];

        // Determine stock status
        if ($permanentlyOos) {
            $stockStatus['permanently_out_of_stock'] = true;
        }

        if (!$permanentlyOos && !$productIsInStock)
        {
            $stockStatus['temporary_out_of_stock'] = true;
        }

        return $stockStatus; // Return the array with the stock statuses
    }
}
