<?php

namespace PWA\Base\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_BASE_URL = 'pwa_web/general/base_url';
    const XML_PATH_API_BASE_URL = 'pwa_web/general/api_base_url';
    const XML_PATH_PRODUCT_URL_MODE = 'pwa_web/general/product_url_mode';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getBaseUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BASE_URL);
    }

    public function getApiBaseUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_BASE_URL);
    }

    public function getProductUrlMode()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_URL_MODE);
    }
}
