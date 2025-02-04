/**
 * @category Scandiweb
 * @package  Scandiweb_HyvaUi
 * @author   Scandiweb <info@scandiweb.com>
 */

define(["Scandiweb_HyvaUi/js/content-type/block-directive"], function (
    BlockDirectiveBase
) {
    "use strict";
    const $super = BlockDirectiveBase.prototype;

    function BlockDirective(parent, config, stageId) {
        BlockDirectiveBase.call(this, parent, config, stageId);
    }

    BlockDirective.prototype = Object.create($super);

    var _proto = BlockDirective.prototype;

    _proto.getAdditionalBlockAttributes = function getAdditionalBlockAttributes(data) {
        var attributes = {
            title: data.title,
            categories: data.categories,
            products_limit: data.products_limit,
            show_category_count: data.show_category_count,
            view_all_button_text: data.view_all_button_text,
        };

        return attributes;
    };

    return BlockDirective;
});
