<?php

declare(strict_types=1);

/**
 * Pallet tax total model.
 *
 * @category Murergrej
 * @Package Murergrej_PalletShipping
 * @author Abanoub Youssef abanoub.youssef@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 **/

namespace Murergrej\PalletShipping\Model\Total\Quote;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Directory\Model\Currency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Murergrej\PalletShipping\Model\Carrier\AbstractCarrier;
use Murergrej\PalletShipping\Model\Carrier\DSV;
use Magento\Store\Model\StoreManagerInterface;
use Murergrej\PalletShipping\Model\Entity\Attribute\Source\Pallet as PalletAttribute;

class PalletTax extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $_code = 'pallet_tax';
    public const BASE_PALLETS = 0.00;
    public const BASE_COST = 0.00;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DSV
     */
    protected $carrier;

    public function __construct(StoreManagerInterface $storeManager, DSV $carrier)
    {
        $this->storeManager = $storeManager;
        $this->carrier = $carrier;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $total->setTotalAmount($this->getCode(), 0);
        $total->setBaseTotalAmount($this->getCode(), 0);
        $total->setPalletTax(0);
        $total->setBasePalletTax(0);

        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $palletCount = 0.0;
        $palletCost = 0.0;

        // Loop through all items in the shipping assignment
        foreach ($shippingAssignment->getItems() as $item) {
            // Skip child items to avoid double-counting
            if ($item->getParentItem()) {
                continue;
            }
            // Get the product and quantity
            $product = $item->getProduct();
            $quantity = $item->getQty();
            // Check if the product is a configurable product
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                // Process all child items of the configurable product
                $childItems = $item->getChildren();
                foreach ($childItems as $childItem) {
                    $childProduct = $childItem->getProduct();
                    // Use the parent configurable product's quantity for calculations
                    $this->calculatePalletCosts($childProduct, $quantity, $palletCount, $palletCost);
                }
            } else {
                // Process simple products or other product types directly
                $this->calculatePalletCosts($product, $quantity, $palletCount, $palletCost);
            }
        }

        // Calculate pallet tax (example: 25% VAT)
        $palletTax = $palletCost * 0.25;
        $basePalletTax = $this->convertPriceToBase($palletTax);

        // Update totals
        $total->setTotalAmount($this->getCode(), $palletTax);
        $total->setBaseTotalAmount($this->getCode(), $basePalletTax);
        $total->setPalletTax($palletTax);
        $total->setBasePalletTax($basePalletTax);

        // Store pallet data in the quote
        $quote->setData('pallet_count', $palletCount);
        $quote->setData('pallet_cost', $palletCost);

        return $this;
    }


    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $palletTax = $total->getPalletTax();
        $palletCost = (float)($quote->getData('pallet_cost') ?? 0.0);
        $palletCount = (float)($quote->getData('pallet_count') ?? 0.0);

        if (!$palletTax) {
            return [];
        }

        $title = __('Pallet Tax');

        if ($palletCount > 0) {
            $title = __('<strong>%1</strong> Palle(r)', number_format($palletCount, 2));
        }

        return [
            'code' => $this->getCode(),
            'title' => $title,
            'value' => $palletCost,
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return Phrase
     */
    public function getLabel()
    {
        return __('Pallet Tax');
    }

    /**
     * @param Address $address
     * @return bool
     */
    protected function hasPalletProduct($address)
    {
        /** @var Item $item */
        foreach ($address->getAllItems() as $item) {
            $product = $item->getProduct();
            if ($product->getIsPallet() === PalletAttribute::VALUE_SINGLE_PALLET
                || $product->getWeight() >= AbstractCarrier::FORCE_IS_SINGLE_PALLET_WEIGHT) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $price
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function convertPriceToBase($price): float
    {
        $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency();
        $baseCurrency = $this->storeManager->getStore()->getBaseCurrency();
        if ($currentCurrency->getCode() === $baseCurrency->getCode()) {
            return $price;
        }
        return $currentCurrency->convert($price, $baseCurrency);
    }

    /**
     * Calculate and accumulate pallet count and cost.
     *
     * @param Product $product
     * @param float $quantity
     * @param float $palletCount
     * @param float $palletCost
     */
    private function calculatePalletCosts(
        Product $product,
        float $quantity,
        float &$palletCount,
        float &$palletCost
    ): void {
        $productWeight = $product->getWeight() * $quantity;
        $isPallet = (int)$product->getData('is_pallet');

        // If the product is not a pallet, skip it
        if ($isPallet === PalletAttribute::VALUE_NO) {
            return;
        }

        // Handle pallet logic based on product type
        switch ($isPallet) {
            case PalletAttribute::VALUE_QUARTER_PALLET:
                $palletCount += 0.25 * $quantity;
                $palletCost += 50 * $quantity;
                break;

            case PalletAttribute::VALUE_HALF_PALLET:
                $palletCount += 0.5 * $quantity;
                $palletCost += 85 * $quantity;
                break;

            case PalletAttribute::VALUE_SINGLE_PALLET:
                $palletCount += $quantity;
                $palletCost += 170 * $quantity;
                break;

            case PalletAttribute::VALUE_PALLET_BY_WEIGHT:
                $weightCalculation = $this->calculatePalletsAndCost($productWeight);
                $palletCount += $weightCalculation['pallets'];
                $palletCost += $weightCalculation['cost'];
                break;

            default:
                break;
        }
    }

    /**
     * @param float $weight
     * @return array
     */
    private function calculatePalletsAndCost(float $weight): array
    {
        // Base case variables for predefined ranges
        $pallets = self::BASE_PALLETS;
        $cost = self::BASE_COST;

        if ($weight >= 65 && $weight <= 100) {
            $pallets = 0.25;
            $cost = 50;
        } elseif ($weight > 100 && $weight <= 200) {
            $pallets = 0.5;
            $cost = 85;
        } elseif ($weight > 200 && $weight <= 1210) {
            $pallets = 1;
            $cost = 170;
        } elseif ($weight > 1210) {
            // For weights greater than 1210 kg, add 1 pallet and 170 DKK, then recursively handle remaining weight
            $pallets = 1;
            $cost = 170;

            // Recursive call for the remaining excess weight
            $remainingWeight = $weight - 1210;
            $additional = $this->calculatePalletsAndCost($remainingWeight);

            // Add the additional pallets and costs from the recursive call
            $pallets += $additional['pallets'];
            $cost += $additional['cost'];
        }
        // Return the total pallets and cost for this weight
        return [
            'pallets' => $pallets,
            'cost' => $cost
        ];
    }
}

