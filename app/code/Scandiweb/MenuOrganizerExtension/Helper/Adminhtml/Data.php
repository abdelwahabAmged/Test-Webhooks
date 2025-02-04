<?php
/**
 * @category  Scandiweb
 * @author    Amr Osama <amr.osama@scandiweb.com | info@scandiweb.com>
 * @copyright Copyright (c) 2023 Scandiweb, Inc (https://scandiweb.com)
 * @license   http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
namespace Scandiweb\MenuOrganizerExtension\Helper\Adminhtml;

use ScandiPWA\MenuOrganizer\Helper\Adminhtml\Data as SourceData;

/**
 * Class Data
 * This class is preferenced over the original class in the ScandiPWA_MenuOrganizer module
 * Reason: To add a new url type to the Item model
 */
class Data extends SourceData
{
    /**
     * Item's URL types
     */
    const TYPE_CUSTOM_URL = 0;
    const TYPE_CMS_PAGE = 1;
    const TYPE_CATEGORY = 2;
    const TYPE_PRODUCT = 3;

    /**
     * Prepare available item url types
     *
     * @return array
     */
    public function getUrlTypes()
    {
        return [
            self::TYPE_CUSTOM_URL => __('Custom URL'),
            self::TYPE_CMS_PAGE => __('CMS Page'),
            self::TYPE_CATEGORY => __('Category'),
            self::TYPE_PRODUCT => __('Product')
        ];
    }
}
