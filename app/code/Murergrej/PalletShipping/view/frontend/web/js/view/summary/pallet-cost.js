/**
 * Pallet Cost Summary Component
 *
 * This component is responsible for displaying the pallet cost in the Luma checkout order summary.
 * It extends the Magento abstract total component and retrieves the pallet cost from the quote totals.
 *
 * @category Murergrej
 * @package Murergrej_PalletShipping
 * @author Abanoub Youssef
 * @contact abanoub.youssef@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Murergrej_PalletShipping/summary/pallet-cost',
            title: 'Palle(r)'
        },

        /**
         * Get pure pallet cost value from totals.
         *
         * @return {*}
         */
        getPureValue: function () {
            var totals = quote.getTotals()();

            if (totals && totals.total_segments) {
                var palletCostSegment = totals.total_segments.find(segment => segment.code === 'pallet_cost');
                return palletCostSegment ? palletCostSegment.value : 0;
            }

            return 0;
        },

        /**
         * Get formatted pallet cost value.
         *
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        },

        /**
         * Check if pallet cost is displayed.
         *
         * @return {Boolean}
         */
        isDisplayed: function () {
            return this.getPureValue() > 0;
        }
    });
});
