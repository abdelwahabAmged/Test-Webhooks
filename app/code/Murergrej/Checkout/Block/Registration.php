<?php

/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Abanoub Youssef <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\Checkout\Block;

use Magento\Customer\Model\Url;

/**
 * Class Registration
 *
 * Custom functionality for the registration block in the checkout process.
 */
class Registration
{
    /**
     * @var Url
     */
    protected Url $_customerUrl;

    /**
     * Registration constructor.
     *
     * @param Url $customerUrl
     */
    public function __construct(
        Url $customerUrl
    ) {
        $this->_customerUrl = $customerUrl;
    }

    /**
     * Retrieve form posting URL
     *
     * @return string
     */
    public function getPostActionUrl(): string
    {
        return $this->_customerUrl->getRegisterPostUrl();
    }
}
