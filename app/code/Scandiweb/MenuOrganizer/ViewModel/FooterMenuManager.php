<?php

/**
 * @category    Scandiweb
 * @author      Amr osama <amr.osama@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Scandiweb\MenuOrganizer\ViewModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use ScandiPWA\MenuOrganizer\Model\MenuFactory;
use ScandiPWA\MenuOrganizer\Api\Data\ItemInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CatCollectionFactory;
use ScandiPWA\MenuOrganizer\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use ScandiPWA\MenuOrganizer\Model\ResourceModel\Menu as MenuResourceModel;

class FooterMenuManager implements ArgumentInterface
{
    public const FOOTER_MENU_IDENTIFIER = "footer-menu";
    /** @var MenuFactory */
    protected MenuFactory $menuFactory;

    /** @var MenuResourceModel */
    protected MenuResourceModel $menuResourceModel;

    /** @var ItemCollectionFactory */
    protected ItemCollectionFactory $itemCollectionFactory;

    /** @var StoreManagerInterface */
    protected StoreManagerInterface $storeManager;

    /** @var CatCollectionFactory */
    protected CatCollectionFactory $catCollectionFactory;

    /** @var PageCollectionFactory */
    protected PageCollectionFactory $pageCollectionFactory;

    /** @var UrlInterface */
    protected UrlInterface $urlBuilder;

    /**
     * Menu constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param MenuFactory $menuFactory
     * @param MenuResourceModel $menuResourceModel
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param CatCollectionFactory $catCollectionFactory
     * @param PageCollectionFactory $pageCollectionFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        MenuFactory $menuFactory,
        MenuResourceModel $menuResourceModel,
        ItemCollectionFactory $itemCollectionFactory,
        CatCollectionFactory $catCollectionFactory,
        PageCollectionFactory $pageCollectionFactory,
        UrlInterface $urlBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->menuFactory = $menuFactory;
        $this->menuResourceModel = $menuResourceModel;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->catCollectionFactory = $catCollectionFactory;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getMenu(): ?array
    {
        $menu = $this->menuFactory->create()->setStoreId(
            $this->storeManager->getStore()->getId()
        );

        $this->menuResourceModel->load($menu, self::FOOTER_MENU_IDENTIFIER);

        if ($menu->getId() === null) {
            return null;
        }

        $mainMenu = $menu->getData();


        return $this->getMenuItems($menu['menu_id']);
    }

    /**
     * @param string $menuId
     * @return array
     */
    private function getMenuItems(string $menuId): array
    {
        $menuItems = $this->itemCollectionFactory
            ->create()
            ->addMenuFilter($menuId)
            ->addStatusFilter()
            ->setParentIdOrder()
            ->setPositionOrder()
            ->getData();

        $allItems = $menuItems;

        $menuColumns = [];

        $menuItems = [];

        foreach ($allItems as $itemId => $item) {
            if (!$item['url']) {
                $item['url'] = "#";
            }

            if (!$item['parent_id'] && $item['item_class'] === "column") {
                $menuColumns[$item['position']] = $item;
            } else {
                $menuItems[$itemId] = $item;
            }
        }

        return array_map(function ($column) use ($menuItems) {
            return [
                "menu" => $column,
                "items" => array_filter($menuItems, function ($item) use ($column) {
                    return $item['parent_id'] === $column['item_id'];
                })
            ];
        }, array_values($menuColumns));
    }
}
