<?php

namespace Murergrej\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
class ForbiddenScript extends AbstractHelper
{
    const FORBIDDEN_DOMAIN = 'admin.murergrej.dk';

    public function isCurrentDomainAllowedForScripts()
    {
        $currentUrl = $this->_getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $isCurrentUrlForbidden = false;
        $forbiddenUrls = $this->getForbiddenDomains();
        foreach ($forbiddenUrls as $forbiddenUrl) {
            $isCurrentUrlForbidden = stripos($currentUrl, $forbiddenUrl);
        }

        return $isCurrentUrlForbidden;

    }

    public function getForbiddenDomains()
    {
        return [self::FORBIDDEN_DOMAIN];
    }
}
