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

    _proto.getAdditionalBlockAttributes = function getAdditionalBlockAttributes(
        data
    ) {
        var attributes = {
            block_title: data.block_title,
            block_subtitle: data.block_subtitle,
            ...(data.dynamic_slides_slider &&
            data.dynamic_slides_slider.length > 0
                ? {
                      cards: JSON.stringify(data.dynamic_slides_slider),
                  }
                : {}),
        };

        return attributes;
    };

    return BlockDirective;
});
