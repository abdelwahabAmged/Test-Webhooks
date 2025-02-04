/**
 * @category Scandiweb
 * @package  Scandiweb_HyvaUi
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
        const appearance = data.appearance;
        const tidyLink = linkHelper.prototype.tidyLink;
        var attributes = {
            appearance,
        };

        if (appearance === "banner_a") {
            const bannerAdditionalAttributes = {
                banner_title_1: data.banner_title_1,
                banner_subtitle_1: data.banner_subtitle_1,
                link_url_1: JSON.stringify(tidyLink(data.link_url_1)),
                ...(data.banner_image_1_src[0]
                    ? { banner_image_1_src: data.banner_image_1_src[0].url }
                    : {}),
                banner_image_1_alt: data.banner_image_1_alt,
            };

            Object.assign(attributes, bannerAdditionalAttributes);
        }

        if (appearance === "banner_b") {
            const bannerAdditionalAttributes = {
                banner_title_1: data.banner_title_1,
                banner_subtitle_1: data.banner_subtitle_1,
                link_url_1: JSON.stringify(tidyLink(data.link_url_1)),
                ...(data.banner_image_1_src[0]
                    ? { banner_image_1_src: data.banner_image_1_src[0].url }
                    : {}),
                banner_image_1_alt: data.banner_image_1_alt,
                ...(data.banner_image_2_src[0]
                    ? { banner_image_2_src: data.banner_image_2_src[0].url }
                    : {}),
                banner_image_2_alt: data.banner_image_2_alt,
            };

            Object.assign(attributes, bannerAdditionalAttributes);
        }

        if (appearance === "banner_c") {
            const bannerAdditionalAttributes = {
                banner_title_1: data.banner_title_1,
                banner_subtitle_1: data.banner_subtitle_1,
                link_url_1: JSON.stringify(tidyLink(data.link_url_1)),
                ...(data.banner_image_1_src[0]
                    ? { banner_image_1_src: data.banner_image_1_src[0].url }
                    : {}),
                banner_image_1_alt: data.banner_image_1_alt,
                ...(data.banner_image_2_src[0]
                    ? { banner_image_2_src: data.banner_image_2_src[0].url }
                    : {}),
                banner_image_2_alt: data.banner_image_2_alt,
                banner_title_2: data.banner_title_2,
                banner_subtitle_2: data.banner_subtitle_2,
                link_url_2: JSON.stringify(tidyLink(data.link_url_2)),
            };

            Object.assign(attributes, bannerAdditionalAttributes);
        }

        if (appearance === "banner_d") {
            const bannerAdditionalAttributes = {
                banner_title_1: data.banner_title_1,
                banner_title_2: data.banner_title_2,
                banner_title_3: data.banner_title_3,
                banner_title_4: data.banner_title_4,
                link_url_1: JSON.stringify(tidyLink(data.link_url_1)),
                link_url_2: JSON.stringify(tidyLink(data.link_url_2)),
                link_url_3: JSON.stringify(tidyLink(data.link_url_3)),
                link_url_4: JSON.stringify(tidyLink(data.link_url_4)),
                ...(data.banner_image_1_src[0]
                    ? { banner_image_1_src: data.banner_image_1_src[0].url }
                    : {}),
                banner_image_1_alt: data.banner_image_1_alt,
                ...(data.banner_image_2_src[0]
                    ? { banner_image_2_src: data.banner_image_2_src[0].url }
                    : {}),
                banner_image_2_alt: data.banner_image_2_alt,
                ...(data.banner_image_3_src[0]
                    ? { banner_image_3_src: data.banner_image_3_src[0].url }
                    : {}),
                banner_image_3_alt: data.banner_image_3_alt,
                ...(data.banner_image_4_src[0]
                    ? { banner_image_4_src: data.banner_image_4_src[0].url }
                    : {}),
                banner_image_4_alt: data.banner_image_4_alt,
            };

            Object.assign(attributes, bannerAdditionalAttributes);
        }

        if (appearance === "banner_e") {
            const bannerAdditionalAttributes = {
                banner_main_title: data.banner_main_title,
                banner_title_1: data.banner_title_1,
                banner_title_2: data.banner_title_2,
                banner_title_3: data.banner_title_3,
                banner_title_4: data.banner_title_4,
                banner_title_5: data.banner_title_5,
                banner_title_6: data.banner_title_6,
                banner_title_7: data.banner_title_7,
                banner_title_8: data.banner_title_8,
                banner_title_9: data.banner_title_9,
                banner_title_10: data.banner_title_10,
                link_url_1: JSON.stringify(tidyLink(data.link_url_1)),
                link_url_2: JSON.stringify(tidyLink(data.link_url_2)),
                link_url_3: JSON.stringify(tidyLink(data.link_url_3)),
                link_url_4: JSON.stringify(tidyLink(data.link_url_4)),
                link_url_5: JSON.stringify(tidyLink(data.link_url_5)),
                link_url_6: JSON.stringify(tidyLink(data.link_url_6)),
                link_url_7: JSON.stringify(tidyLink(data.link_url_7)),
                link_url_8: JSON.stringify(tidyLink(data.link_url_8)),
                link_url_9: JSON.stringify(tidyLink(data.link_url_9)),
                link_url_10: JSON.stringify(tidyLink(data.link_url_10)),
                ...(data.banner_image_1_src[0]
                    ? { banner_image_1_src: data.banner_image_1_src[0].url }
                    : {}),
                banner_image_1_alt: data.banner_image_1_alt,
                ...(data.banner_image_2_src[0]
                    ? { banner_image_2_src: data.banner_image_2_src[0].url }
                    : {}),
                banner_image_2_alt: data.banner_image_2_alt,
                ...(data.banner_image_3_src[0]
                    ? { banner_image_3_src: data.banner_image_3_src[0].url }
                    : {}),
                banner_image_3_alt: data.banner_image_3_alt,
                ...(data.banner_image_4_src[0]
                    ? { banner_image_4_src: data.banner_image_4_src[0].url }
                    : {}),
                banner_image_4_alt: data.banner_image_4_alt,
                ...(data.banner_image_5_src[0]
                    ? { banner_image_5_src: data.banner_image_5_src[0].url }
                    : {}),
                banner_image_5_alt: data.banner_image_5_alt,
                ...(data.banner_image_6_src[0]
                    ? { banner_image_6_src: data.banner_image_6_src[0].url }
                    : {}),
                banner_image_6_alt: data.banner_image_6_alt,
                ...(data.banner_image_7_src[0]
                    ? { banner_image_7_src: data.banner_image_7_src[0].url }
                    : {}),
                banner_image_7_alt: data.banner_image_7_alt,
                ...(data.banner_image_8_src[0]
                    ? { banner_image_8_src: data.banner_image_8_src[0].url }
                    : {}),
                banner_image_8_alt: data.banner_image_8_alt,
                ...(data.banner_image_9_src[0]
                    ? { banner_image_9_src: data.banner_image_9_src[0].url }
                    : {}),
                banner_image_9_alt: data.banner_image_9_alt,
                ...(data.banner_image_10_src[0]
                    ? { banner_image_10_src: data.banner_image_10_src[0].url }
                    : {}),
                banner_image_10_alt: data.banner_image_10_alt,
            };

            Object.assign(attributes, bannerAdditionalAttributes);
        }

        return attributes;
    };

    return BlockDirective;
});
