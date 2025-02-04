<?php

/**
 * @category    Scandiweb
 * @author      Amr osama <amr.osama@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Scandiweb\MenuOrganizer\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use ScandiPWA\MenuOrganizer\Model\Item as MenuItem;
use ScandiPWA\MenuOrganizer\Model\Menu as MenuModel;
use ScandiPWA\MenuOrganizer\Model\MenuFactory;
use ScandiPWA\MenuOrganizer\Model\ItemFactory;

class CreateDefaultFooterMenu implements DataPatchInterface
{
    public const FOOTER_MENU = [
        "title" => "Footer menu",
        "identifier" => "footer-menu"
    ];
    public const FOOTER_MENU_ITEMS = [
        1 => [
            "title" => "Shop",
            "items" => [
                [
                    "title" => "What's new",
                    "url" => "#"
                ],
                [
                    "title" => "Women",
                    "url" => "/women.html"
                ],
                [
                    "title" => "Men",
                    "url" => "/men.html"
                ],
                [
                    "title" => "Gear",
                    "url" => "#"
                ],
                [
                    "title" => "Training",
                    "url" => "#"
                ],
                [
                    "title" => "Sale",
                    "url" => "#"
                ]
            ]
        ],
        2 => [
            "title" => "Customer service",
            "items" => [
                [
                    "title" => "Shipment of your order",
                    "url" => "#"
                ],
                [
                    "title" => "Payment options",
                    "url" => "#"
                ],
                [
                    "title" => "Returns and replacements",
                    "url" => "#"
                ],
                [
                    "title" => "Warranty and repairs",
                    "url" => "#"
                ],
                [
                    "title" => "Get in touch",
                    "url" => "#"
                ]
            ]
        ],
        3 => [
            "title" => "Our company",
            "items" => [
                [
                    "title" => "About us",
                    "url" => "/about"
                ],
                [
                    "title" => "Blog",
                    "url" => "/blog"
                ],
                [
                    "title" => "Our stored",
                    "url" => "#"
                ],
                [
                    "title" => "Sustainability",
                    "url" => "#"
                ]
            ]
        ],
        4 => [
            "title" => "My account",
            "items" => [
                [
                    "title" => "My account",
                    "url" => "/customer/account"
                ],
                [
                    "title" => "My orders",
                    "url" => "sales/order/history"
                ]
            ]
        ]
    ];

    /**
     * @var MenuModel
     */
    protected MenuModel $menu;

    /**
     * @var MenuItem
     */
    protected MenuItem $item;

    /**
     * @var MenuFactory
     */
    protected MenuFactory $menuFactory;

    /**
     * @var ItemFactory
     */
    protected ItemFactory $itemFactory;

    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     *
     * @param MenuModel $menu
     * @param MenuItem $item
     * @param MenuFactory $menuFactory
     * @param ItemFactory $itemFactory
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        MenuModel $menu,
        MenuItem $item,
        MenuFactory $menuFactory,
        ItemFactory $itemFactory,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
    ) {
        $this->menu = $menu;
        $this->item = $item;
        $this->menuFactory = $menuFactory;
        $this->itemFactory = $itemFactory;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        try {
            $identifier = self::FOOTER_MENU['identifier'];
            $title = self::FOOTER_MENU['title'];
            $newMenuId = $this->createMenu($title, $identifier);
            $menuColumns = self::FOOTER_MENU_ITEMS;

            foreach ($menuColumns as $position => $column) {
                $parentId = $this->createMenuItem($column['title'], "#", $position, $newMenuId, 0);

                foreach ($column['items'] as $index => $item) {
                    $this->createMenuItem($item['title'], $item['url'], $index, $newMenuId, $parentId);
                }
            }


            $this->activateMenu($newMenuId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param string $menuTitle
     * @param string $identifier
     * @return int
     * @throws \Exception
     */
    public function createMenu(string $menuTitle, string $identifier): int
    {
        $createdMenu = $this->menuFactory->create()
            ->setIdentifier($identifier)
            ->setTitle($menuTitle)
            ->setIsActive('1')
            ->save();

        return (int)$createdMenu->getId();
    }

    /**
     * @param string $title
     * @param string $url
     * @param int $position
     * @param int $menuId
     * @param int $parentId
     * @return int
     * @throws \Exception
     */
    public function createMenuItem(string $title, string $url, int $position, int $menuId, int $parentId = 0): int
    {
        $identifier = strtolower(str_replace(' ', '_', $title) . '_' . $position);
        $item = $this->itemFactory->create();

        $columnClass = $parentId === 0 ? 'column' : null;

        $item->setIdentifier($identifier)
            ->setMenuId($menuId)
            ->setTitle($title)
            ->setUrl($url)
            ->setPosition($position)
            ->setOpenType(0)
            ->setUrlType(0)
            ->setIsActive(1)
            ->setParentId($parentId)
            ->setItemClass($columnClass);
        $item->save();

        return (int)$item->getId();
    }

    /**
     * @param int $id
     */
    public function activateMenu(int $id): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('scandiweb_menumanager_menu_store');
        $mainTable = $connection->getTableName('scandiweb_menumanager_menu');
        $sql = 'INSERT INTO ' . $tableName . ' (menu_id, store_id) values 
                ((select menu_id from ' . $mainTable . ' where ' . $mainTable . ' .menu_id = ' . $id . '), 0)';
        $connection->query($sql);
    }
}
