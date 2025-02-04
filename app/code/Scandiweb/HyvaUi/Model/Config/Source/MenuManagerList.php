<?php

/**
 * @category    Scandiweb
 * @author      Amr Osama <amr.osama@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Scandiweb\HyvaUi\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

use ScandiPWA\MenuOrganizer\Model\ResourceModel\Menu\CollectionFactory as MenuCollection;

class MenuManagerList implements OptionSourceInterface
{
	protected MenuCollection $menuCollection;

	/**
	 * @param MenuCollection $menuCollection
	 */
	public function __construct(MenuCollection $menuCollection)
	{
		$this->menuCollection = $menuCollection;
	}

	/**
	 * @return array
	 */
	public function toOptionArray(): array
	{
		$collection = $this->menuCollection->create()->getData();
        $menus = [];

		$menus[] = array(
			'value' => '',
			'label' => __('-- Please Select --')
		);

        foreach ($collection as $menu) {
            $name = $menu['title'];
            $identifier = $menu['identifier'];
			$menuId = $menu['menu_id'];

            if (isset($name) && isset($identifier)) {
                $suffix = '';

                if (!$menu['is_active']) {
                    $suffix = __('(Inactive)');
                }

                $menus[] = [
                    'value' => $menuId,
                    'label' => __($name) . ' | ' . $identifier . ' ' . $suffix
                ];
            }
        }

        return $menus;
	}
}
