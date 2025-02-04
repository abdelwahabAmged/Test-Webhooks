<?php
/**
 * @category    Mera
 * @author      Arman Fayziev arman.fayziev@scandiweb.com|<info@scandiweb.com>
 * @copyright   Copyright (c) 2022 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Scandiweb\HyvaUi\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Model\ResourceModel\Block as ResourceBlock;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\StoreRepository;

class CreateProductCardBlock implements DataPatchInterface
{
    const BLOCK_ID = 'hyva_ui_product_card';
    const BLOCK_TITLE = 'Block for Widget with Products';

    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var ResourceBlock
     */
    private $blockResource;

    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * @param BlockRepository $blockRepository
     * @param BlockFactory $blockFactory
     * @param ResourceBlock $blockResource
     * @param StoreRepository $storeRepository
     */
    public function __construct(
        BlockRepository $blockRepository,
        BlockFactory $blockFactory,
        ResourceBlock $blockResource,
        StoreRepository $storeRepository,
    ) {
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
        $this->blockResource = $blockResource;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws StoreIsInactiveException
     * @throws LocalizedException
     */
    public function apply()
    {
        $store = $this->storeRepository->getActiveStoreByCode(0);

        $cmsBlock = $this->blockFactory->create();
        $this->blockResource->load($cmsBlock, self::BLOCK_ID);

        if (!$cmsBlock->getId()) {
            $cmsBlock->setIdentifier(self::BLOCK_ID);
        }

        $cmsBlock->setContent('{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="12" template="Magento_CatalogWidget::product/widget/content/grid.phtml" cache_lifetime="2592000" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`any`,`value`:`1`,`new_child`:`` ^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`3` ^],`1--2`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`4` ^]^]"}}');

        $cmsBlock->setTitle(self::BLOCK_TITLE);
        $cmsBlock->setStores([$store->getCode()]);

        $this->blockRepository->save($cmsBlock);
    }
}
