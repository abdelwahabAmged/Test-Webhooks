<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

namespace DV\ProfitMetrics\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use mysql_xdevapi\Expression;

/**
 * Class Settings
 * @package DV\ProfitMetrics\Model\Config
 */
class Settings
{
    const XML_PATH_MODULE_ENABLED = 'profit_metrics/general/enabled';
    const XML_PATH_BUY_PRICE_ATTRIBUTE = 'profit_metrics/general/buy_price_attribute';
    const XML_PATH_FEED_FILE_NAME = 'profit_metrics/general/feed_filename';
    const XML_PATH_PROFIT_METRICS_ID = 'profit_metrics/general/profit_metrics_id';
    const XML_PATH_SECRET_CODE = 'profit_metrics/general/secret_code';

    const XML_PATH_CRON_FREQUENCY = 'profit_metrics/cron/frequency';
    const XML_PATH_CRON_TIME = 'profit_metrics/cron/time';

    const XML_PATH_ORDERS_SEND_CRON_FREQUENCY = 'profit_metrics/cron/orders_send_frequency';
    const XML_PATH_ORDERS_SEND_CRON_TIME = 'profit_metrics/cron/order_send_time';
    private const XML_PATH_ORDER_STATUSES = 'profit_metrics/general/order_statuses';
    private const XML_PATH_ALL_PAGES_JAVASCRIPT = 'profit_metrics/general/all_pages_javascript';
    private const XML_PATH_TRACKING_DATA_LIFETIME = 'profit_metrics/cron/tracking_data_lifetime';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Settings constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled() : bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            self::XML_PATH_MODULE_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param int|string|null $store
     * @return string
     */
    public function getBuyPriceAttribute($store = null) : string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_BUY_PRICE_ATTRIBUTE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int|string|null $store
     * @return string
     */
    public function getFeedFileName($store = null) : string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_FEED_FILE_NAME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int|string|null $store
     * @return string
     */
    public function getSecretCode($store = null) : string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_SECRET_CODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getProfitMetricsId($store = null) : string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PROFIT_METRICS_ID,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string|null $store
     * @return string
     */
    public function getCronFrequency(string $store = null) : string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CRON_FREQUENCY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string|null $store
     * @return string
     */
    public function getOrderSendCronFrequency(string $store = null):string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_ORDERS_SEND_CRON_FREQUENCY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string|null $store
     * @return string
     */
    public function getCronTime(string $store = null) : string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CRON_TIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
    /**
     * @param string|null $store
     * @return string
     */
    public function getOrderSendCronTime(string $store = null) : string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_ORDERS_SEND_CRON_TIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string|null $store
     * @return array
     */
    public function getOrderStatusesToSend(string $store = null) : array
    {
        $orderStatusesCommaSeparated = (string) $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_STATUSES,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return  $orderStatusesCommaSeparated ? explode(',', $orderStatusesCommaSeparated) : [];
    }

    /**
     * @param string|null $store
     * @return string
     */
    public function getAllPagesJavascript(string $store = null) : string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_ALL_PAGES_JAVASCRIPT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getTrackingDataLifetimeDays(string $store = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_TRACKING_DATA_LIFETIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
