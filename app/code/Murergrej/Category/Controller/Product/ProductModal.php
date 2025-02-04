<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Category
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Category\Controller\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View;
use Magento\Store\Model\ScopeInterface;
use Magento\Swatches\Model\Swatch;
use Murergrej\Hyva\Block\GetDeliveryDaysFromWarehouse;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ConfigurableProduct\Helper\Data as ConfigHelperData;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Framework\Locale\Format;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Helper\Data as TaxData;
use Magento\Catalog\Model\Product;
use Magento\Swatches\Helper\Media;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\Swatches\Helper\Data as SwatchData;

/**
 * Class ProductModal
 */
class ProductModal extends Action
{
    protected $resultFactory;

    /**
     * @var ProductInterface|Product|null
     */
    private Product|ProductInterface|null $product = null;

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param GetDeliveryDaysFromWarehouse $deliveringDaysFromWarehouse
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $productTypeConfig
     * @param Data $wishlistData
     * @param Registry $registry
     * @param View $productViewBlock
     * @param ArrayUtils $arrayUtils
     * @param StoreManagerInterface $storeManager
     * @param ConfigHelperData $helper
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param Format $localeFormat
     * @param Prices $variationPrices
     * @param EncoderInterface $jsonEncoder
     * @param PriceCurrencyInterface $priceCurrency
     * @param TaxData $taxData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param UrlBuilder $imageUrlBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        protected ProductRepositoryInterface $productRepository,
        protected GetDeliveryDaysFromWarehouse $deliveringDaysFromWarehouse,
        protected ScopeConfigInterface $scopeConfig,
        protected ConfigInterface $productTypeConfig,
        protected Data $wishlistData,
        protected Registry $registry,
        protected View $productViewBlock,
        protected ArrayUtils $arrayUtils,
        protected StoreManagerInterface $storeManager,
        protected ConfigHelperData $helper,
        protected ConfigurableAttributeData $configurableAttributeData,
        protected Format $localeFormat,
        protected Prices $variationPrices,
        protected EncoderInterface $jsonEncoder,
        protected PriceCurrencyInterface $priceCurrency,
        protected TaxData $taxData,
        protected SwatchData $swatchHelper,
        protected Media $swatchMediaHelper,
        protected UrlBuilder $imageUrlBuilder,
        protected LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $productId = $this->getRequest()->getParam('id');

            if (!$productId) {
                return $this->resultFactory->create(ResultFactory::TYPE_RAW)
                    ->setContents('Product ID is missing.');
            }

            $updateParams = $this->wishlistData->getUpdateParams($this->registry->registry('wishlist_item'));

            // Fetch product
            try {
                $product = $this->productRepository->getById($productId);
                $this->product = $product;
            } catch (NoSuchEntityException $e) {
                return $this->resultFactory->create(ResultFactory::TYPE_RAW)
                    ->setContents('Product not found.');
            }

            // Fetch default quantity
            $defaultQty = $this->productViewBlock->getProductDefaultQty($product);

            // Prepare response layout
            $response = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $layout = $response->addHandle('hyva_modal_config')->getLayout();

            $attributes = $this->arrayUtils
                ->decorateArray($product->getTypeInstance()->getConfigurableAttributes($product));

            $block = $layout->createBlock(View::class)
                ->setTemplate('Murergrej_Category::product_modal_content.phtml')
                ->setData('delivering_days', $this->deliveringDaysFromWarehouse->getRemainingDays($product))
                ->setData('should_display_stock_status', $this->shouldDisplayProductStockStatus())
                ->setData('product_type_config', !$this->productTypeConfig->isProductSet($product->getTypeId()))
                ->setData('wishlist_update_params', $updateParams)
                ->setData('product_default_qty', $defaultQty)
                ->setData('config_product_attributes', $attributes)
                ->setData('json_config', $this->getJsonConfig())
                ->setData('json_swatch_config', $this->getJsonSwatchConfig())
                ->setData('product', $product);

            $this->getResponse()->setBody($block->toHtml());
        } catch (LocalizedException $e) {
            $this->logger->error('Localized exception in Quickshop modal execution: ' . $e->getMessage());
            return $this->resultFactory->create(ResultFactory::TYPE_RAW)
                ->setContents('An error occurred: ' . $e->getMessage());
        } catch (\Throwable $e) {
            // Catch any other unexpected exceptions
            $this->logger->critical('Critical error in Quickshop modal execution', ['exception' => $e]);
            return $this->resultFactory->create(ResultFactory::TYPE_RAW)
                ->setContents('An unexpected error occurred in Quickshop modal. Please try again later.');
        }
    }

    /**
     * @return array
     */
    protected function getOptionWeight(): array
    {
        $weights = [];
        $allowProducts = $this->product->getTypeInstance()->getUsedProducts($this->product, null);

        foreach ($allowProducts as $product) {
            $weights[$product->getId()] = (float)$product->getWeight();
        }

        return $weights;
    }

    /**
     * @return bool
     */
    public function shouldDisplayProductStockStatus(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/display_product_stock_status',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig(): string
    {
        $currentProduct = $this->product;
        $store = $this->storeManager->getStore();

        $options = $this->helper->getOptions(
            $currentProduct,
            $currentProduct->getTypeInstance()
            ->getUsedProducts($currentProduct, null)
        );
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => $this->variationPrices->getFormattedPrices($currentProduct->getPriceInfo()),
            'optionWeight' => $this->getOptionWeight(),
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => $this->getOptionImages(),
            'index' => isset($options['index']) ? $options['index'] : [],
            'salable' => $options['salable'] ?? [],
            'canDisplayShowOutOfStockStatus' => $options['canDisplayShowOutOfStockStatus'] ?? false
        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, []);

        return $this->jsonEncoder->encode($config);
    }

    /**
     * Get Swatch config data
     *
     * @return string
     */
    public function getJsonSwatchConfig(): string
    {
        $attributesData = $this->getSwatchAttributesData();
        $allOptionIds = $this->getConfigurableOptionsIds($attributesData);
        $swatchesData = $this->swatchHelper->getSwatchesByOptionsId($allOptionIds);

        $config = [];
        foreach ($attributesData as $attributeId => $attributeDataArray) {
            if (isset($attributeDataArray['options'])) {
                $config[$attributeId] = $this->addSwatchDataForAttribute(
                    $attributeDataArray['options'],
                    $swatchesData,
                    $attributeDataArray
                );
            }
            if (isset($attributeDataArray['additional_data'])) {
                $config[$attributeId]['additional_data'] = $attributeDataArray['additional_data'];
            }
        }

        return $this->jsonEncoder->encode($config);
    }

    /**
     * Get swatch attributes data.
     *
     * @return array
     */
    protected function getSwatchAttributesData(): array
    {
        return $this->swatchHelper->getSwatchAttributesAsArray($this->product);
    }

    /**
     * Get configurable options ids.
     *
     * @param array $attributeData
     * @return array
     * @since 100.0.3
     */
    protected function getConfigurableOptionsIds(array $attributeData): array
    {
        $ids = [];
        foreach ($this->product->getTypeInstance()->getUsedProducts($this->product, null) as $product) {
            /** @var Attribute $attribute */
            foreach ($this->helper->getAllowAttributes($this->product) as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                if (isset($attributeData[$productAttributeId])) {
                    $ids[$product->getData($productAttribute->getAttributeCode())] = 1;
                }
            }
        }

        return array_keys($ids);
    }

    /**
     * Add Swatch Data for attribute
     *
     * @param array $options
     * @param array $swatchesCollectionArray
     * @param array $attributeDataArray
     * @return array
     */
    protected function addSwatchDataForAttribute(
        array $options,
        array $swatchesCollectionArray,
        array $attributeDataArray
    ): array
    {
        $result = [];
        foreach ($options as $optionId => $label) {
            if (isset($swatchesCollectionArray[$optionId])) {
                $result[$optionId] = $this->extractNecessarySwatchData($swatchesCollectionArray[$optionId]);
                $result[$optionId] = $this->addAdditionalMediaData($result[$optionId], $optionId, $attributeDataArray);
                $result[$optionId]['label'] = $label;
            }
        }

        return $result;
    }

    /**
     * Retrieve Swatch data for config
     *
     * @param array $swatchDataArray
     * @return array
     */
    protected function extractNecessarySwatchData(array $swatchDataArray): array
    {
        $result['type'] = $swatchDataArray['type'];

        if ($result['type'] == Swatch::SWATCH_TYPE_VISUAL_IMAGE && !empty($swatchDataArray['value'])) {
            $result['value'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_IMAGE_NAME,
                $swatchDataArray['value']
            );
            $result['thumb'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_THUMBNAIL_NAME,
                $swatchDataArray['value']
            );
        } else {
            $result['value'] = $swatchDataArray['value'];
        }

        return $result;
    }

    /**
     * Add media from variation
     *
     * @param array $swatch
     * @param integer $optionId
     * @param array $attributeDataArray
     * @return array
     */
    protected function addAdditionalMediaData(array $swatch, $optionId, array $attributeDataArray): array
    {
        if (isset($attributeDataArray['use_product_image_for_swatch'])
            && $attributeDataArray['use_product_image_for_swatch']
        ) {
            $variationMedia = $this->getVariationMedia($attributeDataArray['attribute_code'], $optionId);
            if (! empty($variationMedia)) {
                $swatch['type'] = Swatch::SWATCH_TYPE_VISUAL_IMAGE;
                $swatch = array_merge($swatch, $variationMedia);
            }
        }

        return $swatch;
    }

    /**
     * Generate Product Media array
     *
     * @param string $attributeCode
     * @param integer $optionId
     * @return array
     */
    protected function getVariationMedia($attributeCode, $optionId): array
    {
        $variationProduct = $this->swatchHelper->loadFirstVariationWithSwatchImage(
            $this->product,
            [$attributeCode => $optionId]
        );

        if (!$variationProduct) {
            $variationProduct = $this->swatchHelper->loadFirstVariationWithImage(
                $this->product,
                [$attributeCode => $optionId]
            );
        }

        $variationMediaArray = [];
        if ($variationProduct) {
            $variationMediaArray = [
                'value' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_IMAGE_NAME),
                'thumb' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_THUMBNAIL_NAME),
            ];
        }

        return $variationMediaArray;
    }

    /**
     * Get swatch product image.
     *
     * @param Product $childProduct
     * @param string $imageType
     * @return string
     */
    protected function getSwatchProductImage(Product $childProduct, $imageType): string
    {
        if ($this->isProductHasImage($childProduct, Swatch::SWATCH_IMAGE_NAME)) {
            $swatchImageId = $imageType;
            $imageAttributes = ['type' => Swatch::SWATCH_IMAGE_NAME];
        } elseif ($this->isProductHasImage($childProduct, 'image')) {
            $swatchImageId = $imageType == Swatch::SWATCH_IMAGE_NAME ? 'swatch_image_base' : 'swatch_thumb_base';
            $imageAttributes = ['type' => 'image'];
        }

        if (!empty($swatchImageId) && !empty($imageAttributes['type'])) {
            return $this->imageUrlBuilder->getUrl($childProduct->getData($imageAttributes['type']), $swatchImageId);
        }

        return '';
    }

    /**
     * Check if product have image.
     *
     * @param Product $product
     * @param string $imageType
     * @return bool
     */
    protected function isProductHasImage(Product $product, $imageType): bool
    {
        return $product->getData($imageType) !== null && $product->getData($imageType) != SwatchData::EMPTY_IMAGE_VALUE;
    }

    /**
     * Get product images for configurable variations
     *
     * @return array
     * @since 100.1.10
     */
    protected function getOptionImages(): array
    {
        $images = [];
        foreach ($this->product->getTypeInstance()->getUsedProducts($this->product, null) as $product) {
            $productImages = $this->helper->getGalleryImages($product) ?: [];

            foreach ($productImages as $image) {
                $images[$product->getId()][] =
                    [
                        'thumb' => $image->getData('small_image_url'),
                        'img' => $image->getData('medium_image_url'),
                        'full' => $image->getData('large_image_url'),
                        'caption' => $image->getLabel(),
                        'position' => $image->getPosition(),
                        'isMain' => $image->getFile() == $product->getImage(),
                        'type' =>  $image->getMediaType() ? str_replace('external-', '', $image->getMediaType()) : '',
                        'videoUrl' => $image->getVideoUrl(),
                    ];
            }
        }

        return $images;
    }

    /**
     * Collect price options
     *
     * @return array
     */
    protected function getOptionPrices(): array
    {
        $prices = [];
        foreach ($this->product->getTypeInstance()->getUsedProducts($this->product, null) as $product) {
            $priceInfo = $product->getPriceInfo();

            $prices[$product->getId()] = [
                'baseOldPrice' => [
                    'amount' => $this->localeFormat->getNumber(
                        $priceInfo->getPrice('regular_price')->getAmount()->getBaseAmount()
                    ),
                ],
                'oldPrice' => [
                    'amount' => $this->localeFormat->getNumber(
                        $priceInfo->getPrice('regular_price')->getAmount()->getValue()
                    ),
                ],
                'basePrice' => [
                    'amount' => $this->localeFormat->getNumber(
                        $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount()
                    ),
                ],
                'finalPrice' => [
                    'amount' => $this->localeFormat->getNumber(
                        $priceInfo->getPrice('final_price')->getAmount()->getValue()
                    ),
                ],
                'tierPrices' => $this->getTierPricesByProduct($product),
                'msrpPrice' => [
                    'amount' => $this->localeFormat->getNumber(
                        $this->priceCurrency->convertAndRound($product->getMsrp())
                    ),
                ],
            ];
        }

        return $prices;
    }

    /**
     * Returns product's tier prices list
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getTierPricesByProduct(ProductInterface $product): array
    {
        $tierPrices = [];
        $tierPriceModel = $product->getPriceInfo()->getPrice('tier_price');
        foreach ($tierPriceModel->getTierPriceList() as $tierPrice) {
            $price = $this->taxData->displayPriceExcludingTax() ?
                $tierPrice['price']->getBaseAmount() : $tierPrice['price']->getValue();

            $tierPriceData = [
                'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                'price' => $this->localeFormat->getNumber($price),
                'percentage' => $this->localeFormat->getNumber(
                    $tierPriceModel->getSavePercent($tierPrice['price'])
                ),
            ];

            if ($this->taxData->displayBothPrices()) {
                $tierPriceData['basePrice'] = $this->localeFormat->getNumber(
                    $tierPrice['price']->getBaseAmount()
                );
            }

            $tierPrices[] = $tierPriceData;
        }

        return $tierPrices;
    }
}
