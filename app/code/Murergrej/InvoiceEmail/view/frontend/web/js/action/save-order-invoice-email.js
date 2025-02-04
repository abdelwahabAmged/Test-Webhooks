define([
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/error-processor',
        'mage/url',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, quote, urlBuilder, errorProcessor, urlFormatter, customer, fullScreenLoader) {
        'use strict';

        return {
            /**
             * Save InvoiceEmail in the quote
             *
             * @param invoiceEmail
             */
            save: function (invoiceEmail) {
                if (invoiceEmail) {
                    var url;
                    if (!customer.isLoggedIn()) {
                        url = urlBuilder.createUrl('/guest-carts/:cartId/set-invoice-email', {
                            cartId: quote.getQuoteId()
                        });
                    } else {
                        url = urlBuilder.createUrl('/carts/mine/set-invoice-email', {});
                    }

                    var payload = {
                        cartId: quote.getQuoteId(),
                        invoiceEmail: {
                            invoiceEmail: invoiceEmail
                        }
                    };

                    fullScreenLoader.startLoader();
                    $.ajax({
                        url: urlFormatter.build(url),
                        data: JSON.stringify(payload),
                        global: false,
                        contentType: 'application/json',
                        type: 'POST',
                        async: false
                    }).done(
                        function (response) {
                            fullScreenLoader.stopLoader();
                        }
                    ).fail(
                        function (response) {
                            fullScreenLoader.stopLoader();
                            errorProcessor.process(response);
                        }
                    );
                }
            }
        };
    });
