/**
 * @category Scandiweb
 * @package  Scandiweb_HyvaUi
 * @author   Scandiweb <info@scandiweb.com>
 */

define([
    'jquery',
    'knockout',
    'Magento_PageBuilder/js/content-type/preview',
    'Magento_PageBuilder/js/config'
], function ($, _knockout, PreviewBase, Config) {
    var Preview = function() {
        PreviewBase.apply(this, arguments);
        this.data['selectedCategories'] = _knockout.observableArray();
    };

    Preview.prototype = Object.create(PreviewBase.prototype);

    Preview.prototype.constructor = Preview;

    Preview.prototype.afterObservablesUpdated = function() {
        PreviewBase.prototype.afterObservablesUpdated.call(this);

        var url = Config.getConfig("preview_url");
        const requestConfig = {
            method: "POST",
            data: {
                role: this.config.name,
                categories: this.data.categories.html(),
                show_category_count: this.data.show_category_count.html()
            },
        };

        $.ajax(url, requestConfig).done(function(response) {
            this.data.selectedCategories(response?.data || []);
        }.bind(this));
    };

    return Preview;
});
