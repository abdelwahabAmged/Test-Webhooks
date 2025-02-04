<?php
namespace PWA\PriceShape\Cron;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class PricesUpdate
{
    public const CONFIG_MODULE_IS_ENABLED = 'murergrej_priceshape/general/enable';

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Curl $curl
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Curl $curl,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->curl = $curl;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(): void
    {
        if (!$this->scopeConfig->getValue(self::CONFIG_MODULE_IS_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return;
        }

        $URL = 'https://app.priceshape.dk/api/json/products?auth-token=a905927d8b9d46383eefc27dfe589135&with_new_prices';

        try {
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->get($URL);
            $res = json_decode($this->curl->getBody(), true);
            $products = $res['items'] ?? null;

            if(!is_array($products)) {
                return;
            }

            foreach($products as $product) {
                if (!isset($product['gid'], $product['new_price']) || !is_numeric($product['new_price'])) {
                    continue;
                }
                $target = $this->productRepository->get($product['gid']);
                $target->setPrice($product['new_price'] * 0.8);
                $this->productRepository->save($target);
            }
        } catch (Exception $e) {
            $this->logger->critical('PriceShape Import Error', ['exception' => $e]);
        }
    }
}
