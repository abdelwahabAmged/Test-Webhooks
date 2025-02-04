<?php
/**
 * Registration file for the Murergrej_Catalog module
 * @category   Murergrej
 * @author     Abanoub Youssef <Abanoub.youssef@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Murergrej_Catalog',
    __DIR__
);
