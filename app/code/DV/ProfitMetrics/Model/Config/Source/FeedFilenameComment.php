<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

namespace DV\ProfitMetrics\Model\Config\Source;

use DV\ProfitMetrics\Model\Config\Settings;
use DV\ProfitMetrics\Model\ProfitHelper;
use Magento\Config\Model\Config\CommentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FeedFilenameComment
 * @package DV\ProfitMetrics\Model\Config\Source
 */
class FeedFilenameComment implements CommentInterface
{
    /**
     * @var ProfitHelper
     */
    private $profitHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FeedFilenameComment constructor.
     * @param ProfitHelper $profitHelper
     * @param StoreManagerInterface $storeManager
     * @param Settings $settings
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProfitHelper $profitHelper,
        StoreManagerInterface $storeManager,
        Settings $settings,
        LoggerInterface $logger
    ) {
        $this->profitHelper = $profitHelper;
        $this->storeManager = $storeManager;
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        return 'Feeds are stored in the server in the path var/profitmetrics/products_{{store}}.xml, <br/>
        where {{store}} is the store code and is accessible by the URL
        ' . $this->getUrlFeed() . '. <br/>
        Please select the specific file name in your store scope in order to modify the feed name for specific store.';
    }

    /**
     * @return array|string
     */
    private function getUrlFeed()
    {
        $storeData = [];

        if ($stores = $this->storeManager->getStores()) {
            try {
                foreach ($stores as $store) {
                    $stringPath = $this->profitHelper->getFeedFilePath($store);
                    $startIndex = strpos($stringPath, 'profitmetrics');
                    $result = substr_replace($stringPath, 'magento_root/var/', 0, $startIndex);

                    $storeData[] = '<br/> <b>file path:</b> ' . $result;
                    $url = $this->storeManager->getStore()->getBaseUrl() .
                        'profitmetrics/feed/index' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR .
                        $this->settings->getSecretCode($store->getId()) . DIRECTORY_SEPARATOR . 'store' . DIRECTORY_SEPARATOR . $store->getId();

                    $storeData[] = '<b>url:</b> <a target="_blank" href="' . $url . '">' . $url . '</a>';
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }

            return '<br/>' . implode('<br/>', $storeData) . '<br/>';
        }

        return [];
    }
}
