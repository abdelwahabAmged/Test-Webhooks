<?php

namespace PWA\Tracking\Helper;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use PWA\Base\Model\Config;

class Data extends \Magento\Shipping\Helper\Data
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager,
        Config $config,
        UrlInterface $url = null
    ) {
        $this->config = $config;
        parent::__construct($context, $storeManager, $url);
    }

    /**
     * Retrieve tracking url with params
     *
     * @param  string $key
     * @param  \Magento\Sales\Model\Order
     * |\Magento\Sales\Model\Order\Shipment|\Magento\Sales\Model\Order\Shipment\Track $model
     * @param  string $method Optional - method of a model to get id
     * @return string
     */
    protected function _getTrackingUrl($key, $model, $method = 'getId')
    {
        if (!($baseUrl = $this->config->getBaseUrl())) {
            return parent::_getTrackingUrl($key, $model, $method);
        }
        $urlPart = "{$key}:{$model->{$method}()}:{$model->getProtectCode()}";
        if (substr($baseUrl, -1) != '/') {
            $baseUrl .= '/';
        }
        return $baseUrl . 'shipping/tracking/' . urlencode($this->urlEncoder->encode($urlPart));
    }
}
