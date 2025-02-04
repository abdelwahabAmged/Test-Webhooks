<?php

declare(strict_types=1);

/**
 * Add Pallet tax to Tax total model.
 *
 * @category Murergrej
 * @Package Murergrej_PalletShipping
 * @author Abanoub Youssef abanoub.youssef@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 **/

namespace Murergrej\PalletShipping\Model\Total\Quote;

use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Directory\Model\Currency;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterfaceFactory;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Murergrej\PalletShipping\Model\Carrier\DSV;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;

class PalletTaxTax extends CommonTaxCollector
{
    protected $_code;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DSV
     */
    protected $carrier;

    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        CustomerAddressFactory $customerAddressFactory,
        CustomerAddressRegionFactory $customerAddressRegionFactory,
        TaxHelper $taxHelper = null,
        QuoteDetailsItemExtensionInterfaceFactory $quoteDetailsItemExtensionInterfaceFactory = null
    ) {
        parent::__construct($taxConfig, $taxCalculationService, $quoteDetailsDataObjectFactory, $quoteDetailsItemDataObjectFactory, $taxClassKeyDataObjectFactory, $customerAddressFactory, $customerAddressRegionFactory, $taxHelper, $quoteDetailsItemExtensionInterfaceFactory);
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $total->setPalletTaxInclTax(0);
        $total->setBasePalletTaxInclTax(0);

        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        if ($total->getPalletTax() <= 0) {
            return $this;
        }

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, false);
        $baseShippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, true);
        if ($shippingDataObject == null || $baseShippingDataObject == null) {
            return $this;
        }

        $storeId = $quote->getStoreId();

        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$shippingDataObject]);
        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $storeId);
        $taxDetailsItems = $taxDetails->getItems()[self::ITEM_CODE_SHIPPING];

        $baseQuoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$baseShippingDataObject]);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $storeId);
        $baseTaxDetailsItems = $baseTaxDetails->getItems()[self::ITEM_CODE_SHIPPING];

        $taxAmount = $quote->getData('pallet_cost') * ($taxDetailsItems->getTaxPercent() / 100);
        $baseTaxAmount = $quote->getData('pallet_cost') * ($baseTaxDetailsItems->getTaxPercent() / 100);
        $amountInclTax = $total->getPalletTax() + $taxAmount;
        $baseAmountInclTax = $total->getBasePalletTax() + $baseTaxAmount;

        $total->setPalletTaxInclTax($amountInclTax);
        $total->setBasePalletTaxInclTax($baseAmountInclTax);

        $total->addTotalAmount('tax', $taxAmount);
        $total->addBaseTotalAmount('tax', $baseTaxAmount);

        return $this;
    }

    /**
     * Get Pallet Tax label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Pallet Tax');
    }
}

