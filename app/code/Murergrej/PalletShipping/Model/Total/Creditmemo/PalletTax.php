<?php

namespace Murergrej\PalletShipping\Model\Total\Creditmemo;

class PalletTax extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setPalletTax(0);
        $creditmemo->setBasePalletTax(0);
        $palletTax = $creditmemo->getOrder()->getPalletTax();

        if (!$palletTax) {
            return $this;
        }

        $palletTaxInvoiced = 0;
        $basePalletTaxInvoiced = 0;
        foreach ($creditmemo->getOrder()->getInvoiceCollection() as $_invoice) {
            $palletTaxInvoiced += $_invoice->getPalletTax();
            $basePalletTaxInvoiced += $_invoice->getBasePalletTax();
        }

        if (!$palletTaxInvoiced) {
            return $this;
        }

        $palletTaxRefunded = 0;
        $basePalletTaxRefunded = 0;
        foreach ($creditmemo->getOrder()->getCreditmemosCollection() as $_creditmemo) {
            $palletTaxRefunded += $_creditmemo->getPalletTax();
            $basePalletTaxRefunded += $_creditmemo->getBasePalletTax();
        }

        $palletTaxToRefund = max(0, $palletTaxInvoiced - $palletTaxRefunded);
        $basePalletTaxToRefund = max(0, $basePalletTaxInvoiced - $basePalletTaxRefunded);

        $creditmemo->setPalletTax($palletTaxToRefund);
        $creditmemo->setBasePalletTax($basePalletTaxToRefund);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $palletTaxToRefund);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basePalletTaxToRefund);

        return $this;
    }
}
