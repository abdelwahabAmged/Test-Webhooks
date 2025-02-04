<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

declare(strict_types = 1);

namespace DV\ProfitMetrics\Model;

use Magento\Store\Model\Store;

/**
 * Class ProfitHelper
 * @package DV\ProfitMetrics\Model
 */
class ProfitHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const FEED_DIRECTORY_PATH = 'profitmetrics';
    public const CORE_FLAG_KEY_PROFITMETRICS_RUNNING = 'profitmetrics_running';
    public const PRODUCT_BATCH_SIZE = 2000;
    public const IMAGE_SIZE = 265;
    public const PROFITMETRICS_VISITOR_ID_SESSION_KEY = 'profitmetrics_visitor_id';
    public const PROFITMETRICS_UPDATE_TIMESTAMP_SESSION_KEY = 'profitmetrics_update_timestamp';

    /**
     * @var \DV\ProfitMetrics\Model\Config\Settings
     */
    private $settings;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \DV\ProfitMetrics\Model\Config\Settings $settings,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->settings = $settings;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function checkSecretCode(string $code) : bool
    {
        $secretCode = $this->settings->getSecretCode();

        return (bool) $secretCode && $code === $secretCode;
    }

    /**
     * @param Store $store
     * @return string|null
     */
    public function getFeedFilePath(Store $store) : ?string
    {
        try {
            $directoryToExport = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . self::FEED_DIRECTORY_PATH;
            $feedFileName = $this->settings->getFeedFileName($store);

            if ($store && ($storeCode = $store->getCode())) {
                $feedFileName = str_replace('{{store}}', $storeCode, $feedFileName);
            }

            return $directoryToExport . DIRECTORY_SEPARATOR . $feedFileName;
        } catch (\Exception$exception) {
            $this->logger->critical($exception);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getStorePublicId(): string
    {
        return $this->settings->getProfitMetricsId();
    }

    /**
     * @return bool
     */
    public function isOnOrderConfirmationPage(): bool
    {
        return $this->_request->getRouteName() === 'checkout'
            && $this->_request->getControllerName() === 'onepage'
            && $this->_request->getActionName() === 'success';
    }

    /**
     * @return string
     */
    public function getAllPagesJavascript(): string
    {
        return $this->settings->getAllPagesJavascript();
    }
}
