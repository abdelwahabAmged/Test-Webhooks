<?php

declare(strict_types=1);

/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @developer   Abanoub Youssef <info@scandiweb.com>
 *
 * This data patch creates a CMS block for the registration prompt on the order success page.
 */

namespace Murergrej\Checkout\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;

class CreatRegistrationBlock implements DataPatchInterface
{
    /**
     * CMS block identifier
     */
    public const CMS_BLOCK_IDENTIFIER = 'registration_info';

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
                                #html-body [data-pb-style=X39YC4F] {
                                    justify-content: flex-start;
                                    display: flex;
                                    flex-direction: column;
                                    background-position: left top;
                                    background-size: cover;
                                    background-repeat: no-repeat;
                                    background-attachment: scroll;
                                    margin: 0;
                                    padding: 0;
                                }
                            </style>
                            <div
                                data-content-type="row"
                                data-appearance="full-bleed"
                                data-enable-parallax="0"
                                data-parallax-speed="0.5"
                                data-background-images="{}"
                                data-background-type="image"
                                data-video-loop="true"
                                data-video-play-only-visible="true"
                                data-video-lazy-load="true"
                                data-video-fallback-src=""
                                data-element="main"
                                data-pb-style="X39YC4F"
                            >
                                <h2 class="registration-header" data-content-type="heading" data-appearance="default" data-element="main">
                                    Create an account
                                </h2>
                                <h3 class="registration-second-header" data-content-type="heading" data-appearance="default" data-element="main">
                                    Creating an account has many benefits:
                                </h3>
                                <div class="registration-description" data-content-type="text" data-appearance="default" data-element="main">
                                    <p>Check out faster</p>
                                </div>
                                <div class="registration-description" data-content-type="text" data-appearance="default" data-element="main">
                                    <p>Keep more than one address</p>
                                </div>
                                <div class="registration-description" data-content-type="text" data-appearance="default" data-element="main">
                                    <p>Track orders and more</p>
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
                $block->setTitle('Registration-info-for-' . $store->getName())
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
