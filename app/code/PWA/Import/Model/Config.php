<?php

namespace PWA\Import\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_BASE_ENDPOINT = 'pwa_import/general/import_base_endpoint';
    const XML_PATH_KEY = 'pwa_import/general/key';

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
