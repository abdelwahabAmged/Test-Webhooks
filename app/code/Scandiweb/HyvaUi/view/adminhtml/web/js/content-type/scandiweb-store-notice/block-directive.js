/**
 * @category Scandiweb
 * @author   Scandiweb <info@scandiweb.com>
 */

define([
    "Scandiweb_HyvaUi/js/content-type/block-directive",
    "Scandiweb_HyvaUi/js/helper/link",
], function (BlockDirectiveBase, linkHelper) {
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
        const tidyLink = linkHelper.prototype.tidyLink;
        var attributes = {
            content: data.content,
            link_url: JSON.stringify(tidyLink(data.link_url))
        };

        return attributes;
    };

    return BlockDirective;
});
