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
            template: 'Scandiweb_HyvaUi::scandiweb_ui/subscribe.phtml',
            title: data.title,
            subtitle: data.subtitle,
            button_text: data.button_text,
            button_color: data.button_color,
        };

        return attributes;
    };

    return BlockDirective;
});
