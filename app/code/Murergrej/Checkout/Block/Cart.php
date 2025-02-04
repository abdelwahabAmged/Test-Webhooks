<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Checkout\Block;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template\Context;
use Murergrej\HelloRetail\Service\HelloRetailService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Block\Cart as SourceCart;
use Magento\Customer\Model\Session;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Shopping cart block
 *
 * @api
 * @since 100.0.2
 */
class Cart extends SourceCart
{
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Url $catalogUrlBuilder
     * @param CartHelper $cartHelper
     * @param HttpContext $httpContext
     * @param HelloRetailService $helloRetailService
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CheckoutSession $checkoutSession,
        Url $catalogUrlBuilder,
        CartHelper $cartHelper,
        HttpContext $httpContext,
        protected HelloRetailService $helloRetailService,
        protected ScopeConfigInterface $scopeConfig,
        protected ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->connection = $resourceConnection->getConnection();
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $data
        );
    }

    /**
     * Returns recommendations key needed to fetch product SKUs from Hello Retail
     *
     * @return string
     */
    public function getHelloRetailRecommendationsKey(): string
    {
        return (string) $this->_scopeConfig->getValue(
            HelloRetailService::XML_PATH_CART_PAGE_HELLO_RETAIL_RECOMMENDATIONS_KEY,
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
