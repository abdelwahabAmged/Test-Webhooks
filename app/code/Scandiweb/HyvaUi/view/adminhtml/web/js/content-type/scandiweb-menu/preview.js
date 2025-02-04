define([
    'jquery',
    'knockout',
    'Magento_PageBuilder/js/content-type/preview',
    'Magento_PageBuilder/js/config'
], function ($, _knockout, PreviewBase, Config) {
    var Preview = function () {
        PreviewBase.apply(this, arguments);
        this.data['menuItems'] = _knockout.observableArray();
    };

    Preview.prototype = Object.create(PreviewBase.prototype);

    Preview.prototype.constructor = Preview;

    Preview.prototype.afterObservablesUpdated = function () {
        PreviewBase.prototype.afterObservablesUpdated.call(this);

        var url = Config.getConfig("preview_url");
        const requestConfig = {
            method: "POST",
            data: {
                role: this.config.name,
                menu_identifier: this.data.menu_identifier.html(),
            },
        };
        
        $.ajax(url, requestConfig).done(function (response) {
            this.data.menuItems(response?.data || []);
        }.bind(this));
    };

    return Preview;
});
