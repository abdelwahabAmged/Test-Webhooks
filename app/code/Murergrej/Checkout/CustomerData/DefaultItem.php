<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Checkout\CustomerData;

use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Checkout\CustomerData\DefaultItem as SourceDefaultItem;
use Magento\Catalog\Helper\Image;
use Magento\Framework\App\ObjectManager;
use Magento\Msrp\Helper\Data as MsrpData;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Checkout\Helper\Data;
use Magento\Framework\Escaper;
use Magento\Catalog\Helper\Data as CatalogHelper;

/**
 * Default cart item
 */
class DefaultItem extends SourceDefaultItem
{
    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param Image $imageHelper
     * @param MsrpData $msrpHelper
     * @param UrlInterface $urlBuilder
     * @param ConfigurationPool $configurationPool
     * @param Data $checkoutHelper
     * @param Escaper|null $escaper
     * @param ItemResolverInterface|null $itemResolver
     * @param CatalogHelper $catalogHelper
     */
    public function __construct(
        Image $imageHelper,
        MsrpData $msrpHelper,
        UrlInterface $urlBuilder,
        ConfigurationPool $configurationPool,
        Data $checkoutHelper,
        CatalogHelper $catalogHelper,
        Escaper $escaper = null,
        ItemResolverInterface $itemResolver = null
    ) {
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
        $this->catalogHelper = $catalogHelper;

        parent::__construct(
            $imageHelper,
            $msrpHelper,
            $urlBuilder,
            $configurationPool,
            $checkoutHelper,
            $escaper,
            $itemResolver
        );
    }

    /**
     * @inheritdoc
     */
    protected function doGetItemData(): array
    {
        $imageHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'mini_cart_product_thumbnail');
        $productName = $this->escaper->escapeHtml($this->item->getProduct()->getName());

        return [
            'options' => $this->getOptionList(),
            'qty' => $this->item->getQty() * 1,
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'product_id' => $this->item->getProduct()->getId(),
            'product_name' => $productName,
            'product_sku' => $this->item->getProduct()->getSku(),
            'row_total_incl_tax' => $this->item->getRowTotalInclTax(),
            'row_total' => $this->item->getRowTotal(),
            'base_final_price' => $this->item->getProduct()->getPriceInfo()->getPrice("final_price")->getProduct()->getPrice(),
            'price_inc_tax_full' => $this->catalogHelper->getTaxPrice(
                $this->item->getProduct(),
                $this->item->getProduct()->getPriceInfo()->getPrice("final_price")->getProduct()->getPrice(), true),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_price_value' => $this->item->getCalculationPrice(),
            'product_image' => [
                'src' => $imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
            'message' => $this->item->getMessage(),
        ];
    }
}
