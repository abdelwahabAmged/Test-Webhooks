<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

namespace DV\ProfitMetrics\Model;

use DOMDocument;
use DV\ProfitMetrics\Model\Config\Settings;
use DV\ProfitMetrics\Model\Config\Source\ProductsPrice;
use Exception;
use Generator;
use Magento\Bundle\Model\Product\Price as BundlePrice;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SimpleXMLElement;
use Zend_Db_Expr;

/**
 * Class ExportProductData
 * @package DV\ProfitMetrics\Model
 */
class ExportProductData
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var $configurableProductPriceUsed
     */
    private $configurableProductPriceUsed;

    /**
     * @var Image
     */
    private $image;

    /**
     * @var Grouped
     */
    private $grouped;

    /**
     * @var BundlePrice
     */
    private $bundlePrice;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * ExportProductData constructor.
     * @param LoggerInterface $logger
     * @param Settings $settings
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param Escaper $escaper
     * @param DirectoryList $directoryList
     * @param FlagManager $flagManager
     * @param Product $product
     * @param ProductResource $productResource
     * @param GroupedType $grouped
     * @param BundlePrice $bundlePrice
     * @param PriceCurrency $priceCurrency
     * @param Image $image
     */
    public function __construct(
        LoggerInterface $logger,
        Settings $settings,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        Escaper $escaper,
        DirectoryList $directoryList,
        FlagManager $flagManager,
        Product $product,
        ProductResource $productResource,
        Grouped $grouped,
        BundlePrice $bundlePrice,
        PriceCurrency $priceCurrency,
        Image $image
    ) {
        $this->logger = $logger;
        $this->settings = $settings;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->directoryList = $directoryList;
        $this->flagManager = $flagManager;
        $this->product = $product;
        $this->productResource = $productResource;
        $this->image = $image;
        $this->grouped = $grouped;
        $this->bundlePrice = $bundlePrice;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Generate xml files with data from products
     */
    public function exportProductData() : void
    {
        $this->flagManager->getFlagData(ProfitHelper::CORE_FLAG_KEY_PROFITMETRICS_RUNNING);
        $this->flagManager->saveFlag(
            ProfitHelper::CORE_FLAG_KEY_PROFITMETRICS_RUNNING,
            $this->dateTime->timestamp()
        );

        $buyPriceAttribute = $this->settings->getBuyPriceAttribute();

        if ($stores = $this->storeManager->getStores()) {
            foreach ($stores as $store) {
                $this->storeManager->setCurrentStore($store->getCode());
                $productFeed = new SimpleXMLElement("<rss pm-type='gs-1.0'></rss>");
                $channel = $productFeed->addChild('channel');
                $storeCurrency = $store->getCurrentCurrency()->getCode();
                $defaultCurrency = $this->storeManager->getDefaultStoreView()->getBaseCurrency()->getCode();

                foreach ($this->getProductsByStore($store) as $product) {
                    $priceBuyDefault = (float)$product->getData($buyPriceAttribute . '_default');
                    $priceBuy = (float)$product->getData($buyPriceAttribute);
                    $priceBuyCurrency = $defaultCurrency;

                    if ($priceBuy !== $priceBuyDefault) {
                        $priceBuyCurrency = $storeCurrency;
                    }

                    $productItem = $channel->addChild('item');
                    $productItem->addChild('g:id', $product->getId(), 'g');
                    $productItem->addChild('title', str_replace('&', '&amp;',$this->escaper->escapeHtml($product->getName())));
                    $productItem->addChild('g:image_link', $this->getProductImageUrl($product, $store), 'g');
                    $productItem->addChild('link', $product->getProductUrl());
                    $productItem->addChild('g:price', $this->getProductPrice($product, $store), 'g');
                    $productItem->addChild('pm:price_currency', $storeCurrency, 'pm');
                    $productItem->addChild('pm:price_buy', (float)$product->getData($buyPriceAttribute), 'pm');
                    $productItem->addChild('pm:price_buy_currency', $priceBuyCurrency, 'pm');
                    $productItem->addChild('pm:num_stock', (int)$product->getQty(), 'pm');
                }


                $feedFileName = $this->settings->getFeedFileName();
                $storeCode = $store->getCode();
                $feedFileName = str_replace('{{store}}', $storeCode, $feedFileName);

                try {
                    $outdatedDirectoryToExport = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR .
                        ProfitHelper::FEED_DIRECTORY_PATH . DIRECTORY_SEPARATOR;

                    if (file_exists($outdatedDirectoryToExport)) {
                        File::rmdirRecursive($outdatedDirectoryToExport);
                    }

                    $directoryToExport = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR .
                        ProfitHelper::FEED_DIRECTORY_PATH . DIRECTORY_SEPARATOR;
                } catch (\Exception $exception) {
                    $this->logger->critical($exception);
                }

                if (!file_exists($directoryToExport)) {
                    if (!mkdir($directoryToExport) && !is_dir($directoryToExport)) {
                        throw new RuntimeException(sprintf('Directory "%s" was not created', $directoryToExport));
                    }
                }

                $filename = $directoryToExport . $feedFileName;

                $dom = new \DOMDocument('1.0', 'UTF-8');
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                $dom->loadXML($productFeed->asXML());

                $this->flagManager->deleteFlag(ProfitHelper::CORE_FLAG_KEY_PROFITMETRICS_RUNNING);

                file_put_contents($filename, (string)$dom->saveXML());
            }
        }
    }

    /**
     * @param Store $store
     * @return Generator
     */
    private function getProductsByStore(Store $store)
    {
        try {
            $buyPriceAttribute = $this->settings->getBuyPriceAttribute();
            $priceBuyAttribute = $this->productResource->getAttribute($buyPriceAttribute);
        } catch (Exception $exception) {
            $this->logger->error($exception);
        }

        $productsCollection = $this->product->getCollection()
            ->addStoreFilter($store)
            ->setStore($store)
            ->addFieldToFilter('type_id', array('neq' => 'configurable'))
            ->addAttributeToSelect(
                array(
                    'sku',
                    'name',
                    'price',
                    $buyPriceAttribute,
                    $buyPriceAttribute . '_default',
                    'special_price',
                    'special_price_from',
                    'special_price_to',
                    'image',
                    'small_image'
                )
            )
            ->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            )
            ->joinField(
                $buyPriceAttribute . '_default',
                new Zend_Db_Expr($this->productResource->getTable('catalog_product_entity') . '_' . $priceBuyAttribute->getBackendType()),
                'value',
                'entity_id=entity_id',
                new Zend_Db_Expr('{{table}}.attribute_id = ' . $priceBuyAttribute->getId() . ' AND {{table}}.store_id = 0'),
                'left'
            );

        $productsCount = $productsCollection->getSize();
        $pagesCount = ceil($productsCount / ProfitHelper::PRODUCT_BATCH_SIZE);

        for ($page = 1; $page <= $pagesCount; $page++) {

            $productsCollection->clear();
            $productsCollection->setPage($page, ProfitHelper::PRODUCT_BATCH_SIZE);

            foreach ($productsCollection as $product) {
                yield $product;
            }
        }
    }

    /**
     * @param Product $product
     * @return string
     */
    private function getProductImageUrl(Product $product): string
    {
        if ($product && $product->hasImage()) {
            return $this->image->init($product, 'product_base_image')
                ->setImageFile($product->getImage())
                ->resize(ProfitHelper::IMAGE_SIZE)
                ->getUrl();
        }

        return '';
    }

    /**
     * @param Product $product
     * @param Store $store
     * @return string
     */
    private function getProductPrice(Product $product, Store $store)
    {
        switch ($product->getTypeId()) {
            case ProductType::TYPE_BUNDLE :
                $minMaxPrices = $this->bundlePrice->getTotalPrices(
                    $product,
                    null,
                    true,
                    false
                );
                $price = array_shift($minMaxPrices);
                break;
            case GroupedType::TYPE_CODE :
                $groupedChildrenMinimumPrice = INF;
                $childGroupedProducts = $this->grouped->getAssociatedProducts($product);

                foreach ($childGroupedProducts as $groupedChildProduct) {
                    if ($groupedChildProduct->getPrice() < $groupedChildrenMinimumPrice) {
                        $groupedChildrenMinimumPrice = $this->getCurrentOrSpecialPrice($groupedChildProduct);
                    }
                }

                $price = !is_infinite($groupedChildrenMinimumPrice) ? $groupedChildrenMinimumPrice : 0;

                break;
            default:
                $price = $this->getCurrentOrSpecialPrice($product);
                break;
        }

        return sprintf('%5.2f', (float) $this->priceCurrency->convert($price));
    }

    /**
     * @param Product $product
     * @return float
     */
    private function getCurrentOrSpecialPrice(Product $product): float
    {
        if (!($specialPrice = $product->getSpecialPrice())) {
            return $product->getPrice();
        }

        $fromDate = $product->getSpecialFromDate();
        $toDate = $product->getSpecialToDate();
        $price = $product->getPrice();

        if (
            !$toDate
            && (!$fromDate || $fromDate < $this->dateTime->date())
            && $specialPrice < $price
        ) {
            $price = $specialPrice;
        }

        return $price;
    }
}
