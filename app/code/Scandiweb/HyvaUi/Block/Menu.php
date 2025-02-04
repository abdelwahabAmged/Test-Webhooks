<?php
/**
 * @category    Scandiweb
 * @author      Amr Osama <amr.osama@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use ScandiPWA\MenuOrganizer\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;

/**
 * Class Blog
 */
class Menu extends Template implements BlockInterface
{
	/**
	 * @var string
	 */
	protected $_template = 'Scandiweb_HyvaUi::menu/menu.phtml';

	/**
	 * @var ItemCollectionFactory
	 */
	protected ItemCollectionFactory $itemCollectionFactory;


	/**
	 * @param Context $context
	 * @param ItemCollectionFactory $itemCollectionFactory
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		ItemCollectionFactory $itemCollectionFactory,
		array $data = []
	)
	{
		parent::__construct($context, $data);
		$this->itemCollectionFactory = $itemCollectionFactory;
	}

    /**
     * @return array
     */
    public function getMenuItems(): array
    {
		$menuId = (int) $this->getData('menu_identifier') ?? null;

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
