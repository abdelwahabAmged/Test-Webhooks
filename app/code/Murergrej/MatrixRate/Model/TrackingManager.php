<?php

namespace Murergrej\MatrixRate\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class TrackingManager
{
    const XML_PATH_API_KEY = 'carriers/matrixrate/api_key';
    const XML_PATH_USER_ID = 'carriers/matrixrate/user_id';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TrackingApi
     */
    protected $api;

    public function __construct(ScopeConfigInterface $scopeConfig, TrackingApiFactory $apiFactory)
    {
        $this->scopeConfig = $scopeConfig;
        $this->api = $apiFactory->create();
        $this->api->setApiKey($this->scopeConfig->getValue(self::XML_PATH_API_KEY));
        $this->api->setUserID($this->scopeConfig->getValue(self::XML_PATH_USER_ID));
    }

    public function getTrackingInformationList($numbers)
    {
        if (!$this->api->getApiKey() || !$this->api->getUserID()) {
            return null;
        }
        return $this->api->getTrackingInformationList($numbers);
    }
}
