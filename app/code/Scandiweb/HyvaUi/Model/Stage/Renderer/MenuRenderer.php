<?php
/**
 * @category    Scandiweb
 * @author      Amr Osama <amr.osama@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Model\Stage\Renderer;

use ScandiPWA\MenuOrganizer\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use Magento\PageBuilder\Model\Stage\RendererInterface;

class MenuRenderer implements RendererInterface
{
    private ItemCollectionFactory $itemCollectionFactory;

    /**
     * @param ItemCollectionFactory $itemCollectionFactory
     */
    public function __construct(
		ItemCollectionFactory $itemCollectionFactory,
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;

    }

    /**
     * @param array $params
     * @return array
     */
    public function render(array $params): array
    {
            $menuId = $params['menu_identifier'] ?? null;

            if(!$menuId) return [];

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
