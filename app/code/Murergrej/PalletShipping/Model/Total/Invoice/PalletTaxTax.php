<?php

namespace Murergrej\PalletShipping\Model\Total\Invoice;

class PalletTaxTax extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $invoice->setPalletTaxInclTax(0);
        $invoice->setBasePalletTaxInclTax(0);

        $palletTaxToInvoice = $invoice->getPalletTax();
        $basePalletTaxToInvoice = $invoice->getBasePalletTax();

        if (!$palletTaxToInvoice) {
            return $this;
        }

        $palletTax = $invoice->getOrder()->getPalletTax();
        $basePalletTax = $invoice->getOrder()->getBasePalletTax();
        $palletTaxInclTax = $invoice->getOrder()->getPalletTaxInclTax();
        $basePalletTaxInclTax = $invoice->getOrder()->getBasePalletTaxInclTax();

        $taxFactor = $palletTaxInclTax / $palletTax;
        $baseTaxFactor = $basePalletTaxInclTax / $basePalletTax;

        $palletTaxToInvoiceInclTax = $palletTaxToInvoice * $taxFactor;
        $basePalletTaxToInvoiceInclTax = $basePalletTaxToInvoice * $baseTaxFactor;

        $invoice->setPalletTaxInclTax($palletTaxToInvoiceInclTax);
        $invoice->setBasePalletTaxInclTax($basePalletTaxToInvoiceInclTax);

        $taxAmount = $palletTaxToInvoiceInclTax - $palletTaxToInvoice;
        $baseTaxAmount = $basePalletTaxToInvoiceInclTax - $basePalletTaxToInvoice;

        $invoice->setTaxAmount($invoice->getTaxAmount() + $taxAmount);
        $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $baseTaxAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $taxAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTaxAmount);

        return $this;
    }
}
