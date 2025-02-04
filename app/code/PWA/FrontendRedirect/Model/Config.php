<?php

namespace PWA\FrontendRedirect\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_ENABLE = 'pwa_web/frontend_redirect/enable';
    const XML_PATH_URL = 'pwa_web/frontend_redirect/url';
    const XML_PATH_DISABLE_PARAM = 'pwa_web/frontend_redirect/disable_param';
    const XML_PATH_IGNORE_PATHS = 'pwa_web/frontend_redirect/ignore_paths';
    const XML_PATH_CUSTOM_REDIRECTS = 'pwa_web/frontend_redirect/custom_redirects';

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function getEnable()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLE);
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_URL);
    }

    /**
     * @return string
     */
    public function getDisableParameter()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DISABLE_PARAM);
    }

    /**
     * @return array
     */
    public function getIgnorePaths()
    {
        $paths = $this->scopeConfig->getValue(self::XML_PATH_IGNORE_PATHS);
        if (!empty($paths)) {
            $paths = array_map('trim', preg_split('/\r?\n/', $paths));
        } else {
            $paths = [];
        }
        return $paths;
    }

    /**
     * @return array[]
     */
    public function getCustomRedirects()
    {
        $data = $this->scopeConfig->getValue(self::XML_PATH_CUSTOM_REDIRECTS);
        if (empty($data)) {
            return [];
        }
        return array_map(function ($line) {
            return array_map('trim', explode('|', $line));
        }, preg_split('/\r?\n/', $data));
    }
}
