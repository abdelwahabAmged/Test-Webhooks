<?php
/**
 * @category    Murergrej
 * @package     Murergrej_HelloRetail
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
  ComponentRegistrar::MODULE,
    'Murergrej_HelloRetail',
    __DIR__
);
