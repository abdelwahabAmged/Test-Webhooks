<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Irmantas Dvareckas <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Checkout\Block\Cart\Item\Renderer;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable as SourceRenderer;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation as TaxCalculation;


class Configurable extends SourceRenderer
{
    /**
     * @param Context $context
     * @param Configuration $productConfig
     * @param Session $checkoutSession
     * @param ImageBuilder $imageBuilder
     * @param Data $urlHelper
     * @param ManagerInterface $messageManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param Manager $moduleManager
     * @param InterpretationStrategyInterface $messageInterpretationStrategy
     * @param TaxHelper $taxHelper
     * @param TaxCalculation $taxCalculation
     * @param array $data
     * @param ItemResolverInterface|null $itemResolver
     */
    public function __construct(
        Context                         $context,
        Configuration                   $productConfig,
        Session                         $checkoutSession,
        ImageBuilder                    $imageBuilder,
        Data                            $urlHelper,
        ManagerInterface                $messageManager,
        PriceCurrencyInterface          $priceCurrency,
        Manager                         $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        TaxHelper                       $taxHelper,
        TaxCalculation                  $taxCalculation,
        array                           $data = [],
        ItemResolverInterface           $itemResolver = null
    ) {
        $this->taxHelper = $taxHelper;
        $this->taxCalculation = $taxCalculation;

        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $data,
            $itemResolver
        );
    }

    /**
     * @param float $priceInclTax
     * @param Product $product
     * @return float
     */
    public function convertPriceInclToExclTax(float $priceInclTax, Product $product): float
    {
        $taxClassId = $product->getTaxClassId();
        $store = $product->getStoreId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $store);
        $taxRate = $this->taxCalculation->getRate($request->setProductClassId($taxClassId));
        $taxRateDecimal = $taxRate / 100;

        return $priceInclTax / (1 + $taxRateDecimal);
    }
}
