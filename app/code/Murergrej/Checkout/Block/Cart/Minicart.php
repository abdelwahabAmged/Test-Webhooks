<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Checkout\Block\Cart;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Murergrej\HelloRetail\Service\HelloRetailService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Cart sidebar block
 *
 * @api
 * @since 100.0.2
 */
class Minicart extends Template
{
    /**
     * @param Context $context
     * @param HelloRetailService $helloRetailService
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected HelloRetailService $helloRetailService,
        protected ScopeConfigInterface $scopeConfig,
        protected ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->connection = $resourceConnection->getConnection();
        parent::__construct($context, $data);
    }

    /**
     * Returns recommendations key needed to fetch product SKUs from Hello Retail
     *
     * @return string
     */
    public function getHelloRetailRecommendationsKey(): string
    {
        return (string) $this->_scopeConfig->getValue(
            'checkout/sidebar/hr_recommendations_key',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getRecommendedProducts(): array
    {
        return $this->helloRetailService->getRecommendedProducts($this->getHelloRetailRecommendationsKey());
    }

    /**
     * @return array
     */
    public function getFreeShippingMethods() : array
    {
        $sql = $this->connection
            ->select()
            ->from(
                $this->connection->getTableName('webshopapps_matrixrate'),
                ['condition_from_value', 'condition_to_value', 'order_total']
            )->where('dest_zip <= 6630 AND dest_zip_to >= 6630 AND price = 0') // The 6630 is a default zip code to be used in free shipping meter
            ->order(['order_total DESC']);

        return $this->connection->fetchAll($sql);
    }
}
