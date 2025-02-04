<?php

declare(strict_types=1);

/**
 * @category    Murergrej
 * @package     Murergrej_Footer
 * @developer   Abanoub Youssef <info@scandiweb.com>
 *
 * This data patch creates a CMS block for the Ordering content in the footer.
 */

namespace Murergrej\Footer\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;

class CreateOrderingFooterBlock implements DataPatchInterface
{
    /**
     * CMS block identifier
     */
    public const CMS_BLOCK_IDENTIFIER = 'ordering_footer';

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
                                #html-body [data-pb-style=VQMQM9X],
                                #html-body [data-pb-style=WXXLW9I] {
                                    justify-content: flex-start;
                                    display: flex;
                                    flex-direction: column;
                                    background-position: left top;
                                    background-size: cover;
                                    background-repeat: no-repeat;
                                    background-attachment: scroll;
                                }

                                #html-body [data-pb-style=WXXLW9I] {
                                    margin: 0;
                                }

                                #html-body [data-pb-style=DAW4BA8] {
                                    border-style: none;
                                }

                                #html-body [data-pb-style=UGTQNI5],
                                #html-body [data-pb-style=W40BVF4] {
                                    max-width: 100%;
                                    height: auto;
                                }

                                #html-body [data-pb-style=FUEBU5X],
                                #html-body [data-pb-style=G30XEKH],
                                #html-body [data-pb-style=RU60Y7R],
                                #html-body [data-pb-style=WCC2ERC],
                                #html-body [data-pb-style=WYP13CP] {
                                    margin: 0;
                                }

                                #html-body [data-pb-style=RXB6BEL] {
                                    display: inline-block;
                                }

                                #html-body [data-pb-style=ESY94Q1] {
                                    text-align: left;
                                }

                                #html-body [data-pb-style=WFCAA43] {
                                    display: inline-block;
                                }

                                #html-body [data-pb-style=KHC0PYW] {
                                    text-align: left;
                                }

                                #html-body [data-pb-style=X1P6X6U] {
                                    display: inline-block;
                                }

                                #html-body [data-pb-style=VP7NRUE] {
                                    text-align: left;
                                }

                                #html-body [data-pb-style=JNMKGF1] {
                                    display: inline-block;
                                }

                                #html-body [data-pb-style=FDPK502] {
                                    text-align: left;
                                }

                                #html-body [data-pb-style=WRBE0ND] {
                                    display: inline-block;
                                }

                                #html-body [data-pb-style=YMWR3O3] {
                                    text-align: left;
                                }

                                @media only screen and (max-width: 768px) {
                                    #html-body [data-pb-style=VQMQM9X],
                                    #html-body [data-pb-style=WXXLW9I] {
                                        justify-content: flex-start;
                                        display: flex;
                                        flex-direction: column;
                                        background-position: left top;
                                        background-size: cover;
                                        background-repeat: no-repeat;
                                        background-attachment: scroll;
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
                                    data-pb-style="VQMQM9X"
                                >
                                    <h4 class="expandable-item-header" data-content-type="heading" data-appearance="default" data-element="main">
                                        Ordering
                                    </h4>
                                    <figure class="toggle-btn toggle-icon" data-content-type="image" data-appearance="full-width" data-element="main"
                                            data-pb-style="DAW4BA8">
                                        <img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/Chevron_2.png}}" alt="" title=""
                                                data-element="desktop_image" data-pb-style="W40BVF4" />
                                        <img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/Chevron_2.png}}" alt="" title=""
                                                data-element="mobile_image" data-pb-style="UGTQNI5" />
                                    </figure>
                                </div>
                            </div>
                           <div data-content-type="row" data-appearance="contained" data-element="main">
                                <div class="expandable-content"
                                    data-enable-parallax="0"
                                    data-parallax-speed="0.5"
                                    data-background-images="{}"
                                    data-background-type="image"
                                    data-video-loop="true"
                                    data-video-play-only-visible="true"
                                    data-video-lazy-load="true"
                                    data-video-fallback-src=""
                                    data-element="inner"
                                    data-pb-style="WXXLW9I">
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="WCC2ERC"
                                         class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="RXB6BEL">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/betaling" target="_blank" data-link-type="default"
                                                data-element="link" data-pb-style="ESY94Q1">
                                                <span data-element="link_text">Payment methods</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="RU60Y7R"
                                         class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="WFCAA43">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/betaling" target="_blank" data-link-type="default"
                                                data-element="link" data-pb-style="KHC0PYW">
                                                <span data-element="link_text">Returns &amp; Complaints</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="WYP13CP"
                                         class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="X1P6X6U">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/handelsbetingelser" target="_blank"
                                                data-link-type="default" data-element="link" data-pb-style="VP7NRUE">
                                                <span data-element="link_text">Trading conditions</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="G30XEKH"
                                         class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="JNMKGF1">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/levering" target="_blank" data-link-type="default"
                                                data-element="link" data-pb-style="FDPK502">
                                                <span data-element="link_text">Delivery</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="FUEBU5X"
                                         class="expandable-item-link">
                                        <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="WRBE0ND">
                                            <a class="pagebuilder-button-link" href="https://www.murergrej.dk/privatlivs-cookiepolitik" target="_blank"
                                                data-link-type="default" data-element="link" data-pb-style="YMWR3O3">
                                                <span data-element="link_text">Privacy &amp; Cookie Policy</span>
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
