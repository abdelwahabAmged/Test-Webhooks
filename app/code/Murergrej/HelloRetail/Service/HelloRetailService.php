<?php
/**
 * @category    Murergrej
 * @package     Murergrej_HelloRetail
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\HelloRetail\Service;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
use Addwish\Awext\Helper\Config as ConfigHelper;

/**
 * Class HelloRetailService
 *
 * HelloRetail service class
 */
class HelloRetailService
{
    /**
     * Popular Products category page url key
     */
    public const POPULAR_PRODUCTS_PAGE_KEY = 'popular-products';

    /**
     * Since Hello Retail returns all products if hierarchies is not passed in the request, we should set a limit
     */
    public const POPULAR_PRODUCTS_COUNT = 100;

    /**
     * Path to the recommendations key needed for PDP related products slider
     */
    public const XML_PATH_PDP_HELLO_RETAIL_RELATED_RECOMMENDATIONS_KEY =
        'addwish_configuration/product_page/related_products_recommendations_key';

    /**
     * Path to the recommendations key needed for PDP alternative products slider
     */
    public const XML_PATH_PDP_HELLO_RETAIL_ALTERNATIVE_RECOMMENDATIONS_KEY =
        'addwish_configuration/product_page/alternative_products_recommendations_key';

    /**
     * Path to the recommendations key needed for PDP recently viewed products slider
     */
    public const XML_PATH_PDP_HELLO_RETAIL_RECENTLY_VIEWED_RECOMMENDATIONS_KEY =
        'addwish_configuration/product_page/recently_viewed_products_recommendations_key';

    /**
     * Path to the recommendations key needed for 'You may also like' products slider in the cart page
     */
    public const XML_PATH_CART_PAGE_HELLO_RETAIL_RECOMMENDATIONS_KEY =
        'addwish_configuration/cart_page/ymal_products_recommendations_key';

    /**
     * Path to the recommendations key needed for 'You may also like' products slider in the order success page
     */
    public const XML_PATH_SUCCESS_PAGE_HELLO_RETAIL_RECOMMENDATIONS_KEY =
        'addwish_configuration/success_page/success_ymal_products_recommendations_key';

    /**
     * Path to website Uuid
     */
    public const XML_PATH_WEBSITE_UUID = 'addwish_configuration/general/website_uuid';

    /**
     * @param LoggerInterface $logger
     * @param HttpClient $httpClient
     * @param SerializerInterface $serializerInterface
     * @param HttpContext $httpContext
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ConfigHelper $configHelper
     * @param CookieManagerInterface $cookieManager
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected HttpClient $httpClient,
        protected SerializerInterface $serializerInterface,
        protected HttpContext $httpContext,
        protected ProductCollectionFactory $productCollectionFactory,
        protected ConfigHelper $configHelper,
        protected CookieManagerInterface $cookieManager,
        protected ScopeConfigInterface $scopeConfig,
        protected RequestInterface $request
    ) {
    }

    /**
     * @param array $params
     * @return array
     */
    public function getProductSKUs(array $params): array
    {
        if (empty($params)) {
            return [];
        }

        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 1;

        // Determine start and count based on the type of request
        if (!empty($params['view_all'])) {
            // When viewing all products
            $start = $params['start'];

            if (
                !empty($params['is_popular_products_page']) &&
                self::POPULAR_PRODUCTS_COUNT > $params['products_per_page']
            ) {
                $count = self::POPULAR_PRODUCTS_COUNT - $start;
            } else {
                $count = $params['total_count'];
            }
        } else {
            if (!empty($params['is_initial_load'])) {
                $start = 0; // Always start from 0 on the initial load
            } else {
                $start = ($pageNumber - 1) * $params['products_per_page'];
            }

            if (!empty($params['is_popular_products_page'])) {
                if (!empty($params['is_initial_load'])) {
                    $count = min(self::POPULAR_PRODUCTS_COUNT, $params['products_per_page']);
                } else {
                    // Load remaining products based on start and limit to products_per_page
                    $remainingCount = self::POPULAR_PRODUCTS_COUNT - $start;
                    $count = min($remainingCount, $params['products_per_page']);
                }
            } else {
                $count = $params['products_per_page'];
            }
        }

        $data = [
            'url' => $params['category_url'],
            'format' => 'json',
            'firstLoad' => true,
            'trackingUserId' => $this->getTrackingUserId(),
            'params' => [
                'filters' => [
                    // Include hierarchies only if the category URL does NOT contain 'popular-product'
                    ...(str_contains($params['category_url'], self::POPULAR_PRODUCTS_PAGE_KEY) ?
                        [] : ['hierarchies' => $params['hierarchies']])
                ]
            ],
            'products' => [
                'start' => $start,
                'count' => $count,
                'fields' => ['productNumber', 'trackingCode'],
            ],
        ];

        if ($sortParam = $this->request->getParam('sort')) {
            $data['products']['sorting'][] = $sortParam;
        }

        if ($filtersParam = $this->request->getParam('filters')) {
            $filtersArray = explode(',', $filtersParam);

            foreach ($filtersArray as $filter) {
                $data['products']['filters'][] = $filter;
            }
        }

        $pagesKey = $params['page_key'] ?? $this->configHelper->getDefaultPagesKey();

        try {
            $response = $this->httpClient->post("https://core.helloretail.com/serve/pages/$pagesKey", [
                'json' => $data
            ]);

            $responseData = $this->serializerInterface->unserialize($response->getBody());

            if (isset($responseData['products']['result'])) {
                return [
                    'products' => $responseData['products']['result'],
                    'totalCount' => $responseData['products']['total'] ?? 0,
                    'sortOptions' => $responseData['products']['sorting'],
                    'filters' => $responseData['products']['filters']
                ];
            }
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to fetch product numbers: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * @return string
     */
    public function getWebsiteUuid(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_WEBSITE_UUID);
    }

    /**
     * @param string $productSku
     * @param string $productUrl
     * @return void
     */
    public function trackPageView(string $productSku, string $productUrl): void
    {
        $websiteUuid = $this->getWebsiteUuid();

        if (!$websiteUuid) {
            return;
        }

        $data = [
            'location' => $productUrl,
            'trackingUserId' => $this->getTrackingUserId(),
            'websiteUuid' => $websiteUuid,
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'productNumber' => $productSku,
        ];

        try {
            $this->httpClient->post('https://core.helloretail.com/serve/collect/pageview', [
                'json' => $data
            ]);
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to track page view: ' . $e->getMessage());
        }
    }

    /**
     * @param mixed $recommendationKeys
     * @param string|null $productSku
     * @param string|null $productUrl
     * @return array
     */
    public function getRecommendedProducts(
        mixed $recommendationKeys,
        string $productSku = null,
        string $productUrl = null
    ): array
    {
        if (!$recommendationKeys) {
            return [];
        }

        if ($productSku) {
            $this->trackPageView($productSku, $productUrl);
        }

        $requests = [];

        // Check if $recommendationKeys is a string or an array
        if (is_string($recommendationKeys)) {
            $request = [
                'key' => $recommendationKeys,
                'fields' => ['productNumber', 'trackingCode']
            ];

            if ($productSku !== null) {
                $request['context'] = [
                    'productNumbers' => [$productSku]
                ];
            }

            $requests[] = $request;
        } elseif (is_array($recommendationKeys)) {
            foreach ($recommendationKeys as $key) {
                $request = [
                    'key' => $key,
                    'format' => 'json',
                    'fields' => ['productNumber', 'trackingCode']
                ];

                if ($productSku !== null) {
                    $request['context'] = [
                        'productNumbers' => [$productSku]
                    ];
                }

                $requests[] = $request;
            }
        }

        $data = [
            'trackingUserId' => $this->getTrackingUserId(),
            'requests' => $requests
        ];

        try {
            $response = $this->httpClient->post('https://core.helloretail.com/serve/recoms', [
                'json' => $data
            ]);
            $responseData = $this->serializerInterface->unserialize($response->getBody());

            if (isset($responseData['responses']) && is_array($responseData['responses'])) {
                $recommendations = [];

                foreach ($responseData['responses'] as $response) {
                    $productNumbers = [];
                    foreach ($response['products'] as $product) {
                        if (isset($product['productNumber'])) {
                            $productNumbers[] = $product['productNumber'];
                        }
                    }

                    $recommendations[] = [
                        'collection' => $this->filterProductsByNumbers($productNumbers),
                        'products' => $response['products']
                    ];
                }

                return $recommendations;
            }
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to fetch recommendations: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * @param array $productNumbers
     * @return Collection
     */
    protected function filterProductsByNumbers(array $productNumbers): Collection
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*') // Adjust to fetch only needed attributes
        ->addAttributeToFilter('sku', ['in' => $productNumbers]);

        // Apply ordering based on the order of $productNumbers
        $collection->getSelect()->order(
            new \Zend_Db_Expr("FIELD(e.sku, '" . implode("','", $productNumbers) . "')")
        );

        return $collection;
    }

    /**
     * @return string|null
     */
    public function getTrackingUserId(): ?string
    {
        return $this->cookieManager->getCookie('hello_retail_id');
    }
}
