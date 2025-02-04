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
            items_limit: data.items_limit,
            title: data.title,
        };

        return attributes;
    };

    return BlockDirective;
});
