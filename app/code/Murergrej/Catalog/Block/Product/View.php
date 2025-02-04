<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Catalog
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Catalog\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View as SourceView;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Murergrej\HelloRetail\Service\HelloRetailService;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;

/**
 * Product View block
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class View extends SourceView
{
    /**
     * @param Context $context
     * @param EncoderInterface $urlEncoder
     * @param JsonEncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param ScopeConfigInterface $scopeConfig
     * @param HelloRetailService $helloRetailService
     * @param StockItemRepository $stockItemRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        JsonEncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        protected ScopeConfigInterface $scopeConfig,
        protected HelloRetailService $helloRetailService,
        protected StockItemRepository $stockItemRepository,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    /**
     * @return string
     */
    public function getRecommendationsKeyForRelatedProductsSlider(): string
    {
        return (string) $this->scopeConfig->getValue(
            HelloRetailService::XML_PATH_PDP_HELLO_RETAIL_RELATED_RECOMMENDATIONS_KEY
        );
    }

    /**
     * @return string
     */
    public function getRecommendationsKeyForAlternativeProductsSlider(): string
    {
        return (string) $this->scopeConfig->getValue(
            HelloRetailService::XML_PATH_PDP_HELLO_RETAIL_ALTERNATIVE_RECOMMENDATIONS_KEY
        );
    }

    /**
     * @return string
     */
    public function getRecommendationsKeyForRecentlyViewedProductsSlider(): string
    {
        return (string) $this->scopeConfig->getValue(
            HelloRetailService::XML_PATH_PDP_HELLO_RETAIL_RECENTLY_VIEWED_RECOMMENDATIONS_KEY
        );
    }

    /**
     * @param mixed $recommendationKeys
     * @return array
     */
    public function getRecommendedProducts(mixed $recommendationKeys): array
    {
        $product = $this->getProduct();

        return $this->helloRetailService
            ->getRecommendedProducts(
                $recommendationKeys,
                $product->getSku(),
                $product->getProductUrl()
            );
    }
}
