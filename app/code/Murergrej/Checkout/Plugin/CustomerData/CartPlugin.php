<?php

declare(strict_types=1);

/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Irmantas Dvareckas <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

namespace Murergrej\Checkout\Plugin\CustomerData;

use Magento\Checkout\CustomerData\Cart as Subject;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Cart\CartTotalRepository;

class CartPlugin
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param TaxHelper $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param CheckoutSession $checkoutSession
     * @param CartTotalRepository $cartTotalRepository
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private TaxHelper $taxHelper,
        private PriceCurrencyInterface $priceCurrency,
        private CheckoutSession $checkoutSession,
        private CartTotalRepository $cartTotalRepository
    ) {}

    /**
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(Subject $subject, array $result): array
    {
        $quote = $this->checkoutSession->getQuote();
        $totals = $this->cartTotalRepository->get($quote->getId());
        $totalWeight = 0;
        $palletVat = 0.25;

        foreach ($result['items'] as &$item) {
            try {
                $product = $this->productRepository->getById($item['product_id']);

                if ($product->getTypeId() == Configurable::TYPE_CODE) {
                    $selectedSimpleProduct = $this->productRepository->get($item['product_sku']);
                    $priceExclTax = $this->priceCurrency->convert($selectedSimpleProduct->getFinalPrice(), true);
                    $totalWeight += $selectedSimpleProduct->getWeight() * $item['qty'];
                } else {
                    $priceExclTax = $this->priceCurrency->convert($product->getFinalPrice(), true);
                    $totalWeight += $product->getWeight() * $item['qty'];
                }

                $item['price_excl_tax'] = $priceExclTax;

            } catch (\Exception $e) {
                continue;
            }
        }
        // Fetch real pallet data from the quote and pass it to the frontend
        $result['pallet_count'] = isset($quote) && $quote->hasData('pallet_count') ? (float)$quote->getData('pallet_count') : 0;
        $result['pallet_cost'] = isset($quote) && $quote->hasData('pallet_cost') ? (float)$quote->getData('pallet_cost') : 0;
        $palletsInclTax = (float)$result['pallet_cost'] + (float)$result['pallet_cost'] * $palletVat;

        $result['discount'] = $totals->getDiscountAmount();
        $result['tax_amount'] = $totals->getTaxAmount();
        $result['subtotalInclTax'] = (float)$totals->getBaseSubtotal() + (float)$result['pallet_cost'] +
            (float)$totals->getTaxAmount() + $totals->getDiscountAmount() ;
        $result['total_weight'] = $totalWeight;

        return $result;
    }
}
