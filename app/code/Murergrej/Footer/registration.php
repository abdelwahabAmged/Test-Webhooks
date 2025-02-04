<?php

/**
 * Registration file for the Murergrej_Footer module.
 *
 * This file registers the Murergrej_Footer module with Magento.
 *
 * @category    Murergrej
 * @package     Murergrej_Footer
 * @developer   Abanoub Youssef <info@scandiweb.com>
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Murergrej_Footer',
    __DIR__
);
