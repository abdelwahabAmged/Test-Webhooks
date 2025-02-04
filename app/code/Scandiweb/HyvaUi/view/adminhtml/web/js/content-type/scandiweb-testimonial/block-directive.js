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
            title: data.title,
            ...(data.author_image_src[0]
                ? { author_image_src: data.author_image_src[0].url }
                : {}),
            author_image_alt: data.author_image_alt,
            author_image_title: data.author_image_title,
            author_content: data.author_content,
            author: data.author,
            company: data.company,
            testimonial_content_background_color:
                data.testimonial_content_background_color,
        };

        return attributes;
    };

    return BlockDirective;
});
