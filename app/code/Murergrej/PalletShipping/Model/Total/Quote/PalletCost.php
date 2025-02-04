<?php

declare(strict_types=1);

/**
 * This class is responsible for adding the pallet cost to the quote totals.
 *
 * @category Murergrej
 * @package Murergrej_PalletShipping
 * @author Abanoub Youssef
 * @contact abanoub.youssef@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

namespace Murergrej\PalletShipping\Model\Total\Quote;

use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;

class PalletCost extends AbstractTotal
{
    /**
     * Collect totals information
     *
     * This method collects the pallet cost from the quote and adds it to the total.
     *
     * @param Quote $quote The quote object
     * @param ShippingAssignmentInterface $shippingAssignment The shipping assignment
     * @param Total $total The total object
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): PalletCost {
        parent::collect($quote, $shippingAssignment, $total);

        // Assuming pallet_cost is calculated elsewhere and set on the quote
        $palletCost = $quote->getPalletCost();
        if ($palletCost) {
            $total->addTotalAmount('pallet_cost', $palletCost);
            $total->addBaseTotalAmount('pallet_cost', $palletCost);
        }

        return $this;
    }

    /**
     * Fetch totals information
     *
     * This method fetches the pallet cost from the quote and returns it as an array.
     *
     * @param Quote $quote The quote object
     * @param Total $total The total object
     * @return array The pallet cost information
     */
    public function fetch(
        Quote $quote,
        Total $total
    ): array {
        $palletCost = $quote->getPalletCost();

        if (!$palletCost) {
            return [];
        }

        return [
            'code' => 'pallet_cost',
            'title' => __('Pallet Cost'),
            'value' => $palletCost,
        ];
    }

    /**
     * Get label
     *
     * This method returns the label for the pallet cost.
     *
     * @return Phrase The label for the pallet cost
     */
    public function getLabel(): Phrase
    {
        return __('Pallet Cost');
    }
}
