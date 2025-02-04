<?php

namespace PWA\Cache\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_BASE_ENDPOINT = 'pwa_cache/general/cache_base_endpoint';
    const XML_PATH_KEY = 'pwa_cache/general/key';

    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getBaseEndpoint()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BASE_ENDPOINT);
    }

    public function getKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_KEY);
    }
}
