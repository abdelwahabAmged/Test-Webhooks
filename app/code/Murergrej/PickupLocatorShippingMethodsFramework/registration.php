<?php
/**
 * @category    Murergrej
 * @package     Murergrej_PickupLocatorShippingMethodsFramework
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2025 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
  ComponentRegistrar::MODULE,
    'Murergrej_PickupLocatorShippingMethodsFramework',
    __DIR__
);
