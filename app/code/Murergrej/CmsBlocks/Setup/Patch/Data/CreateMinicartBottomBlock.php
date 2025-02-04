<?php

/**
 * @category Murergrej
 * @package Murergrej_CmsBlock
 * @author Ernests Verins info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

namespace Murergrej\CmsBlocks\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\State;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\App\Area;
use Scandiweb\Migration\Helper\MediaMigration;

/**
 * Class CreateMinicartBottomBlock
 *
 * This class is responsible for creating CMS blocks during the setup process.
 */
class CreateMinicartBottomBlock implements DataPatchInterface
{
    /**
     * @var BlockFactory
     */
    private BlockFactory $blockFactory;

    /**
     * @var State
     */
    private State $state;

    /**
     * @var BlockRepositoryInterface
     */
    private BlockRepositoryInterface $blockRepository;

    /**
     * @var MediaMigration
     */
    private MediaMigration $mediaMigration;

    /**
     * @var array
     */
    private const MEDIA_FILES = [
        'wysiwyg/minicart_payment_methods.png'
    ];

    /**
     * @param BlockFactory $blockFactory
     * @param State $state
     * @param BlockRepositoryInterface $blockRepository
     * @param MediaMigration $mediaMigration
     */
    public function __construct(
        BlockFactory             $blockFactory,
        State                    $state,
        BlockRepositoryInterface $blockRepository,
        MediaMigration           $mediaMigration
    ) {
        $this->blockFactory = $blockFactory;
        $this->state = $state;
        $this->blockRepository = $blockRepository;
        $this->mediaMigration = $mediaMigration;
    }

    /**
     * Apply method to create CMS blocks.
     *
     * @return $this
     */
    public function apply()
    {
        // Set the area code to avoid errors in non-admin areas
        $this->state->setAreaCode(Area::AREA_ADMINHTML);

        $this->mediaMigration->copyMediaFiles(
            self::MEDIA_FILES,
            'Murergrej_CmsBlocks',
            ''
        );

        $blocks = [
            [
                'title' => 'Minicart Bottom block',
                'identifier' => 'minicart_bottom_block',
                'content' => '
                    <style>
                        #html-body [data-pb-style=AM2KC8E]{justify-content:flex-start;
                        display:flex;flex-direction:column;
                        background-position:left top;background-size:cover;background-repeat:no-repeat;
                        background-attachment:scroll}#html-body [data-pb-style=ULLWKBJ]{text-align:center;
                        margin:-10px -10px -6px;border-style:none}#html-body [data-pb-style=TD2VR46],
                        #html-body [data-pb-style=VNXINC8]{max-width:100%;height:auto}
                        @media only screen and (max-width: 768px) {
                            #html-body [data-pb-style=ULLWKBJ]{border-style:none}
                        }
                    </style>
                    <div data-content-type="row" data-appearance="contained" data-element="main">
                        <div
                            data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}"
                            data-background-type="image" data-video-loop="true" data-video-play-only-visible="true"
                            data-video-lazy-load="true" data-video-fallback-src="" data-element="inner"
                            data-pb-style="AM2KC8E"
                        >
                            <figure
                                data-content-type="image" data-appearance="full-width" data-element="main"
                                data-pb-style="ULLWKBJ"><img class="pagebuilder-mobile-hidden"
                                src="{{media url=wysiwyg/minicart_payment_methods.png}}" alt="" title=""
                                data-element="desktop_image"
                                data-pb-style="TD2VR46"><img class="pagebuilder-mobile-only"
                                src="{{media url=wysiwyg/minicart_payment_methods.png}}" alt="" title=""
                                data-element="mobile_image"
                                data-pb-style="VNXINC8"
                            ></figure>
                        </div>
                    </div>
                ',
                'is_active' => 1,
                'stores' => [0], // 0 means all stores
            ]
        ];

        foreach ($blocks as $data) {
            $block = $this->blockFactory->create()->load($data['identifier'], 'identifier');
            if (!$block->getId()) {
                $block->setData($data);
                $this->blockRepository->save($block);
            }
        }

        return $this;
    }

    /**
     * Retrieve the list of aliases for this patch.
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Retrieve the list of dependencies for this patch.
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
