<?php

/**
 * Module to Override the getJsonCategoryData method to be compatible with Hello Retail
 * @category  Scandiweb
 * @package   Scandiweb_Mageworx
 * @author    Ingy El-Khateeb
 */
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Scandiweb_Mageworx',
    __DIR__
);
