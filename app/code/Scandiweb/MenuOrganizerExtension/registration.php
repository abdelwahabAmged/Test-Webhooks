<?php
/**
 * @category  Scandiweb
 * @author    Amr Osama <amr.osama@scandiweb.com | info@scandiweb.com>
 * @copyright Copyright (c) 2023 Scandiweb, Inc (https://scandiweb.com)
 * @license   http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Scandiweb_MenuOrganizerExtension',
    __DIR__
);
