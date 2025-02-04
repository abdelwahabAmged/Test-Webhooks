<?php

declare(strict_types=1);

/**
 * @category    Murergrej
 * @package     Murergrej_Footer
 * @developer   Abanoub Youssef <info@scandiweb.com>
 *
 * This data patch creates a CMS block for the Customer services content in the footer.
 */

namespace Murergrej\Footer\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;

class CreateCustomerServicesBlock implements DataPatchInterface
{
    /**
     * CMS block identifier
     */
    public const CMS_BLOCK_IDENTIFIER = 'customer_services_footer';

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
                            #html-body [data-pb-style=PWCJY2O] {
                                justify-content: flex-start;
                                display: flex;
                                flex-direction: column;
                                background-position: left top;
                                background-size: cover;
                                background-repeat: no-repeat;
                                background-attachment: scroll;
                            }

                            #html-body [data-pb-style=Q3LWD6C] {
                                border-style: none;
                            }

                            #html-body [data-pb-style=MJIRPMR],
                            #html-body [data-pb-style=Q57M9QI] {
                                max-width: 100%;
                                height: auto;
                            }

                            #html-body [data-pb-style=YS4BN8P] {
                                justify-content: flex-start;
                                display: flex;
                                flex-direction: column;
                                background-position: left top;
                                background-size: cover;
                                background-repeat: no-repeat;
                                background-attachment: scroll;
                                margin: 0;
                            }

                            #html-body [data-pb-style=HLTIV5Q] {
                                display: inline-block;
                            }

                            #html-body [data-pb-style=T28I1C3] {
                                text-align: center;
                                margin: 0;
                            }

                            #html-body [data-pb-style=X7BM75G] {
                                display: inline-block;
                            }

                            #html-body [data-pb-style=K40CWPK] {
                                text-align: center;
                                margin: 0;
                            }

                            #html-body [data-pb-style=YFFWE39] {
                                display: inline-block;
                            }

                            #html-body [data-pb-style=SE5V1T2] {
                                text-align: center;
                                margin: 0;
                            }

                            @media only screen and (max-width: 768px) {
                                #html-body [data-pb-style=Q3LWD6C] {
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
                                    data-pb-style="PWCJY2O"
                                >
                                    <h4 class="expandable-item-header" data-content-type="heading" data-appearance="default" data-element="main">
                                        Customer Services
                                    </h4>
                                    <figure class="toggle-btn toggle-icon" data-content-type="image" data-appearance="full-width" data-element="main"
                                            data-pb-style="Q3LWD6C">
                                        <img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/Chevron_1.png}}" alt="" title=""
                                                data-element="desktop_image" data-pb-style="MJIRPMR" />
                                        <img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/Chevron_1.png}}" alt="" title=""
                                                data-element="mobile_image" data-pb-style="Q57M9QI" />
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
                                    data-pb-style="YS4BN8P"
                                >
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"
                                            class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="HLTIV5Q">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/murergrej-job" target="_blank"
                                                data-link-type="default"
                                                data-element="link" data-pb-style="T28I1C3">
                                                <span data-element="link_text">Contact Us</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"
                                         class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="X7BM75G">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/bestil-en-demo" target="_blank"
                                                data-link-type="default" data-element="link" data-pb-style="K40CWPK">
                                                <span data-element="link_text">Order a demo</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"
                                            class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="YFFWE39">
                                            <a class="pagebuilder-button-link" href="https://murergrej-cgi-1719813521-dev-rxj.readymage.com/style_guide"
                                                target="_blank" data-link-type="default" data-element="link" data-pb-style="SE5V1T2">
                                                <span data-element="link_text">FAQ</span>
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
