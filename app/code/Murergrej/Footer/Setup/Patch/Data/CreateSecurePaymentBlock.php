<?php

declare(strict_types=1);

/**
 * @category    Murergrej
 * @package     Murergrej_Footer
 * @developer   Abanoub Youssef <info@scandiweb.com>
 *
 * This data patch creates a CMS block for the SecurePayment content in the footer.
 */

namespace Murergrej\Footer\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;

class CreateSecurePaymentBlock implements DataPatchInterface
{
    /**
     * CMS block identifier
     */
    public const CMS_BLOCK_IDENTIFIER = 'secure_payment_footer';

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
                          #html-body [data-pb-style=D04XICB],
                          #html-body [data-pb-style=JFYWLCI] {
                            background-position: left top;
                            background-size: cover;
                            background-repeat: no-repeat;
                            background-attachment: scroll;
                          }
                          #html-body [data-pb-style=D04XICB] {
                            justify-content: flex-start;
                            display: flex;
                            flex-direction: column;
                          }
                          #html-body [data-pb-style=JFYWLCI] {
                            align-self: stretch;
                          }
                          #html-body [data-pb-style=VJNIBFS] {
                            display: flex;
                            width: 100%;
                          }
                          #html-body [data-pb-style=HFQ92YS],
                          #html-body [data-pb-style=QBBBI0L] {
                            justify-content: flex-start;
                            display: flex;
                            flex-direction: column;
                            background-position: left top;
                            background-size: auto;
                            background-repeat: no-repeat;
                            background-attachment: scroll;
                            width: 50%;
                            align-self: stretch;
                          }
                          #html-body [data-pb-style=HFQ92YS] {
                            background-size: cover;
                          }
                          #html-body [data-pb-style=E7JSH18] {
                            padding-top: 0;
                            border-style: none;
                          }
                          #html-body [data-pb-style=MVRGM44],
                          #html-body [data-pb-style=UR56IJI] {
                            max-width: 100%;
                            height: auto;
                          }
                          @media only screen and (max-width: 768px) {
                            #html-body [data-pb-style=E7JSH18] {
                              border-style: none;
                            }
                          }
                        </style>
                        <div
                          class="flex"
                          data-content-type="row"
                          data-appearance="full-bleed"
                          data-enable-parallax="1"
                          data-parallax-speed="0.5"
                          data-background-images="{}"
                          data-background-type="image"
                          data-video-loop="true"
                          data-video-play-only-visible="true"
                          data-video-lazy-load="true"
                          data-video-fallback-src=""
                          data-element="main"
                          data-pb-style="D04XICB"
                        >
                          <div
                            class="pagebuilder-column-group gap-2"
                            data-background-images="{}"
                            data-content-type="column-group"
                            data-appearance="default"
                            data-grid-size="2"
                            data-element="main"
                            data-pb-style="JFYWLCI"
                          >
                            <div
                              class="pagebuilder-column-line"
                              data-content-type="column-line"
                              data-element="main"
                              data-pb-style="VJNIBFS"
                            >
                              <div
                                class="pagebuilder-column mr-2 flex flex-col items-center"
                                data-content-type="column"
                                data-appearance="full-height"
                                data-background-images="{}"
                                data-element="main"
                                data-pb-style="QBBBI0L"
                              >
                                <div
                                  class="whitespace-nowrap mb-2 md:mb-0"
                                  data-content-type="text"
                                  data-appearance="default"
                                  data-element="main"
                                >
                                  <p id="W0FUAA7">
                                    <span style="font-size: 12px; color: #72757e;">
                                      Secure payments provided by:
                                    </span>
                                  </p>
                                </div>
                              </div>
                              <div
                                class="pagebuilder-column flex flex-col items-center"
                                data-content-type="column"
                                data-appearance="full-height"
                                data-background-images="{}"
                                data-element="main"
                                data-pb-style="HFQ92YS"
                              >
                                <figure
                                  data-content-type="image"
                                  data-appearance="full-width"
                                  data-element="main"
                                  data-pb-style="E7JSH18"
                                >
                                  <img
                                    class="pagebuilder-mobile-hidden"
                                    src="{{media url=wysiwyg/worldline_12.png}}"
                                    alt=""
                                    title=""
                                    data-element="desktop_image"
                                    data-pb-style="MVRGM44"
                                  />
                                  <img
                                    class="pagebuilder-mobile-only"
                                    src="{{media url=wysiwyg/worldline_12.png}}"
                                    alt=""
                                    title=""
                                    data-element="mobile_image"
                                    data-pb-style="UR56IJI"
                                  />
                                </figure>
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
