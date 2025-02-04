define(
    [
        'jquery',
        'ko',
        'Magento_Ui/js/form/element/abstract',
        'Murergrej_InvoiceEmail/js/action/save-order-invoice-email'
    ],
    function ($, ko, Component, saveOrderInvoiceEmail) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Murergrej_InvoiceEmail/invoice_email',
                listens: {
                    '${ $.provider }:invoiceEmailForm.invoice_email': 'saveInvoiceEmail'
                }
            },
            saveInvoiceEmail: function (value) {
                saveOrderInvoiceEmail.save(value);
            }
        });
    }
);
