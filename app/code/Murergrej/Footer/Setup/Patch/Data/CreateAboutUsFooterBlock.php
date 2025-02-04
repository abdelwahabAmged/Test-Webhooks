<?php

declare(strict_types=1);

/**
 * @category    Murergrej
 * @package     Murergrej_Footer
 * @developer   Abanoub Youssef <info@scandiweb.com>
 *
 * This data patch creates a CMS block for the About-Us content in the footer.
 */

namespace Murergrej\Footer\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;

class CreateAboutUsFooterBlock implements DataPatchInterface
{
    /**
     * CMS block identifier
     */
    public const CMS_BLOCK_IDENTIFIER = 'about_us_footer';

    /**
     * Default store views
     */
    public const DEFAULT_STORES = ['admin', 'default'];

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private BlockFactory $blockFactory;

    /**
     * @var StoreRepositoryInterface
     */
    private StoreRepositoryInterface $storeRepository;

    /**
     * @var BlockRepositoryInterface
     */
    private BlockRepositoryInterface $blockRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory,
        StoreRepositoryInterface $storeRepository,
        BlockRepositoryInterface $blockRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
        $this->storeRepository = $storeRepository;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $blockContent = <<<HTML
                            <style>
                              #html-body [data-pb-style=KX07HB5] {
                                justify-content: flex-start;
                                display: flex;
                                flex-direction: column;
                                background-position: left top;
                                background-size: cover;
                                background-repeat: no-repeat;
                                background-attachment: scroll;
                              }

                              #html-body [data-pb-style=Y6MA29H] {
                                border-style: none;
                              }

                              #html-body [data-pb-style=NDGH62A],
                              #html-body [data-pb-style=OBMB2K0] {
                                max-width: 100%;
                                height: auto;
                              }

                              #html-body [data-pb-style=FANONE0] {
                                justify-content: flex-start;
                                display: flex;
                                flex-direction: column;
                                background-position: left top;
                                background-size: cover;
                                background-repeat: no-repeat;
                                background-attachment: scroll;
                                margin: 0;
                              }

                              #html-body [data-pb-style=YD8S3OX] {
                                margin: 0;
                              }

                              #html-body [data-pb-style=MDOW4IB] {
                                display: inline-block;
                              }

                              #html-body [data-pb-style=JOXM5IQ] {
                                text-align: center;
                              }

                              #html-body [data-pb-style=UE7OAI3] {
                                margin: 0;
                              }

                              #html-body [data-pb-style=O4KH72Y] {
                                display: inline-block;
                              }

                              #html-body [data-pb-style=UV9KPII] {
                                text-align: center;
                              }

                              #html-body [data-pb-style=PVKD33R] {
                                margin: 0;
                              }

                              #html-body [data-pb-style=M0FO3MK] {
                                display: inline-block;
                              }

                              #html-body [data-pb-style=Y7CELSX] {
                                text-align: center;
                              }

                              #html-body [data-pb-style=EUP2FBW] {
                                margin: 0;
                              }

                              #html-body [data-pb-style=YQ53AMQ] {
                                display: inline-block;
                              }

                              #html-body [data-pb-style=JRFNT1C] {
                                text-align: center;
                              }

                              @media only screen and (max-width: 768px) {
                                #html-body [data-pb-style=Y6MA29H] {
                                  border-style: none;
                                }
                              }
                            </style>
                             <div data-content-type="row" data-appearance="contained" data-element="main">
                                <div
                                    class="expandable-wrapper"
                                    data-enable-parallax="0"
                                    data-parallax-speed="0.5"
                                    data-background-images="{}"
                                    data-background-type="image"
                                    data-video-loop="true"
                                    data-video-play-only-visible="true"
                                    data-video-lazy-load="true"
                                    data-video-fallback-src=""
                                    data-element="inner"
                                    data-pb-style="KX07HB5"
                                >
                                    <h4 class="expandable-item-header" data-content-type="heading" data-appearance="default" data-element="main">
                                        About us
                                    </h4>
                                    <figure class="toggle-btn toggle-icon" data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="Y6MA29H">
                                        <img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/Chevron.png}}" alt="" title="" data-element="desktop_image" data-pb-style="OBMB2K0" />
                                        <img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/Chevron.png}}" alt="" title="" data-element="mobile_image" data-pb-style="NDGH62A" />
                                    </figure>
                                </div>
                            </div>
                             <div data-content-type="row" data-appearance="contained" data-element="main">
                                <div
                                    class="expandable-content"
                                    data-enable-parallax="0"
                                    data-parallax-speed="0.5"
                                    data-background-images="{}"
                                    data-background-type="image"
                                    data-video-loop="true"
                                    data-video-play-only-visible="true"
                                    data-video-lazy-load="true"
                                    data-video-fallback-src=""
                                    data-element="inner"
                                    data-pb-style="FANONE0"
                                >
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="YD8S3OX" class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="MDOW4IB">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/om-os" target="_blank" data-link-type="default" data-element="link" data-pb-style="JOXM5IQ">
                                                <span data-element="link_text">Our story</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="UE7OAI3" class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="O4KH72Y">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/om-os" target="_blank" data-link-type="default" data-element="link" data-pb-style="UV9KPII">
                                                <span data-element="link_text">How to shop with us</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="PVKD33R" class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="M0FO3MK">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/vores-butik" target="_blank" data-link-type="default" data-element="link" data-pb-style="Y7CELSX">
                                                <span data-element="link_text">Our store</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="EUP2FBW" class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="YQ53AMQ">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/murergrej-job" target="_blank" data-link-type="default" data-element="link" data-pb-style="JRFNT1C">
                                                <span data-element="link_text">Work with us</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        HTML;
        // Iterate through all stores
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            if ($store->getCode() === 'default') {
                $blockIdentifier = self::CMS_BLOCK_IDENTIFIER;
                // Check if the block already exists
                $existingBlock = $this->blockFactory->create()->load($blockIdentifier, 'identifier');
                if ($existingBlock->getId()) {
                    continue;
                }

                // Create new block
                $block = $this->blockFactory->create();
                $block->setTitle($blockIdentifier.'-' . $store->getName())
                    ->setIdentifier($blockIdentifier)
                    ->setIsActive(true)
                    ->setContent($blockContent)
                    ->setData('stores', [$store->getId()]);

                $this->blockRepository->save($block);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
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

    /**
     * Retrieve the list of aliases for this patch.
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }
}
