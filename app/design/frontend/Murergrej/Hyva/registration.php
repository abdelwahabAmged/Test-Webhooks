<?php
/**
 * @category  Murergrej
 * @package   Murergrej_Hyva
 * @author    Ernests Verins <info@scandiweb.com>
 * @copyright Copyright (c) 2023 Scandiweb, Inc (https://scandiweb.com)
 * @license   http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::THEME,
    'frontend/Murergrej/Hyva',
    __DIR__
);
