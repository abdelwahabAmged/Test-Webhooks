/**
 * @category Scandiweb
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
            appearance: data.appearance,
            usp_title: data.usp_title,
            usp_subtitle: data.usp_subtitle,
            usp_color: data.usp_color,
            ...(data.usp_sections && data.usp_sections.length > 0
                ? {
                      cards: JSON.stringify(data.usp_sections),
                  }
                : {}),
        };

        return attributes;
    };

    return BlockDirective;
});
