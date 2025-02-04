<?php

namespace Murergrej\PalletShipping\Model\Total\Creditmemo;

class PalletTaxTax extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setPalletTaxInclTax(0);
        $creditmemo->setBasePalletTaxInclTax(0);

        $palletTaxToRefund = $creditmemo->getPalletTax();
        $basePalletTaxToRefund = $creditmemo->getBasePalletTax();

        if (!$palletTaxToRefund) {
            return $this;
        }

        $palletTax = $creditmemo->getOrder()->getPalletTax();
        $basePalletTax = $creditmemo->getOrder()->getBasePalletTax();
        $palletTaxInclTax = $creditmemo->getOrder()->getPalletTaxInclTax();
        $basePalletTaxInclTax = $creditmemo->getOrder()->getBasePalletTaxInclTax();

        $taxFactor = $palletTaxInclTax / $palletTax;
        $baseTaxFactor = $basePalletTaxInclTax / $basePalletTax;

        $palletTaxToRefundInclTax = $palletTaxToRefund * $taxFactor;
        $basePalletTaxToRefundInclTax = $basePalletTaxToRefund * $baseTaxFactor;

        $creditmemo->setPalletTaxInclTax($palletTaxToRefundInclTax);
        $creditmemo->setBasePalletTaxInclTax($basePalletTaxToRefundInclTax);

        $taxAmount = $palletTaxToRefundInclTax - $palletTaxToRefund;
        $baseTaxAmount = $basePalletTaxToRefundInclTax - $basePalletTaxToRefund;

        $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $taxAmount);
        $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTaxAmount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $taxAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTaxAmount);

        return $this;
    }
}
