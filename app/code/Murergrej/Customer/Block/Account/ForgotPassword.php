<?php

namespace Murergrej\Customer\Block\Account;

class ForgotPassword extends \Magento\Framework\View\Element\Template
{
    public function getLink()
    {
        return $this->getUrl('*/*/customerforgotpasswordpost');
    }
}
