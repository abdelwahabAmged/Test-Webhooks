<?php

namespace Murergrej\PalletShipping\Model\Total\Invoice;

class PalletTax extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $invoice->setPalletTax(0);
        $invoice->setBasePalletTax(0);
        $palletTax = $invoice->getOrder()->getPalletTax();
        $basePalletTax = $invoice->getOrder()->getBasePalletTax();

        if (!$palletTax) {
            return $this;
        }

        $palletTaxInvoiced = 0;
        $basePalletTaxInvoiced = 0;
        foreach ($invoice->getOrder()->getInvoiceCollection() as $_invoice) {
            $palletTaxInvoiced += $_invoice->getPalletTax();
            $basePalletTaxInvoiced += $_invoice->getBasePalletTax();
        }

        $palletTaxToInvoice = max(0, $palletTax - $palletTaxInvoiced);
        $basePalletTaxToInvoice = max(0, $basePalletTax - $basePalletTaxInvoiced);

        $invoice->setPalletTax($palletTaxToInvoice);
        $invoice->setBasePalletTax($basePalletTaxToInvoice);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $palletTaxToInvoice);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $basePalletTaxToInvoice);

        return $this;
    }
}
