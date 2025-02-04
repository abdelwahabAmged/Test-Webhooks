define([], function () {
    'use strict';

    return function () {
        AdminOrder.prototype.selectAddress = AdminOrder.prototype.selectAddress.wrap(
          function (parentFunction, el, container) {
              parentFunction(el, container);
              var saveInAddressBookCheckBox = jQuery(el).closest('.order-choose-address').find('.order-save-in-address-book input[type="checkbox"]');
              if (el.value == '') {
                  saveInAddressBookCheckBox.prop('disabled', true).prop('checked', false);
              } else {
                  saveInAddressBookCheckBox.prop('disabled', false);
              }
          }
        );

        AdminOrder.prototype.setStoreId = function (id) {
            this.storeId = id;
            this.storeSelectorHide();
            this.sidebarShow();
            //this.loadArea(['header', 'sidebar','data'], true);
            this.dataShow();
            this.loadArea(['header', 'data'], true, null, function () {
                location.reload();
            });
        };

        AdminOrder.prototype.loadArea = function (area, indicator, params, callback) {
            var deferred = new jQuery.Deferred();
            var url = this.loadBaseUrl;
            if (area) {
                area = this.prepareArea(area);
                url += 'block/' + area;
            }
            if (indicator === true) indicator = 'html-body';
            params = this.prepareParams(params);
            params.json = true;
            if (!this.loadingAreas) this.loadingAreas = [];
            if (indicator) {
                this.loadingAreas = area;
                new Ajax.Request(url, {
                    parameters: params,
                    loaderArea: indicator,
                    onSuccess: function (transport) {
                        var response = transport.responseText.evalJSON();
                        this.loadAreaResponseHandler(response);
                        deferred.resolve();
                        if (callback instanceof Function) {
                            callback();
                        }
                    }.bind(this)
                });
            } else {
                new Ajax.Request(url, {
                    parameters: params,
                    loaderArea: indicator,
                    onSuccess: function (transport) {
                        deferred.resolve();
                    }
                });
            }
            if (typeof productConfigure !== 'undefined' && area instanceof Array && area.indexOf('items') !== -1) {
                productConfigure.clean('quote_items');
            }
            return deferred.promise();
        };
    };
});
