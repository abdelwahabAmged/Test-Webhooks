<?php

namespace Murergrej\Tax\Helper;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_XML_PATH_EXCLUDE_SPECIAL_PRICE_TAX = 'tax/display/exclude_special_price_tax';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    public function __construct(Context $context, \Magento\Customer\Model\Session $session)
    {
        parent::__construct($context);
        $this->session = $session;
    }

    public function isExcludeSpecialPriceTaxEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_XML_PATH_EXCLUDE_SPECIAL_PRICE_TAX);
    }

    public function getCustomerIsLoggedIn()
    {
        return $this->session->isLoggedIn() || $this->session->getCustomerGroupId() != \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
    }

    public function checkBlockLocation($block)
    {
        return $block->getData('zone') == 'item_view' || $block->getData('zone') == 'item_list' && $block->getData('list_category_page');
    }
}
