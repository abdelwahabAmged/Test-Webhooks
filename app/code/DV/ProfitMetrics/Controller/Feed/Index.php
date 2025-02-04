<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

namespace DV\ProfitMetrics\Controller\Feed;

use DV\ProfitMetrics\Model\Config\Settings;
use DV\ProfitMetrics\Model\ProfitHelper;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\Raw;

/**
 * Class Index
 * @package DV\ProfitMetrics\Controller\Feed
 */
class Index extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ProfitHelper
     */
    private $profitHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param Settings $settings
     * @param ProfitHelper $profitHelper
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        Settings $settings,
        ProfitHelper $profitHelper,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->settings = $settings;
        $this->profitHelper = $profitHelper;
        $this->logger = $logger;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * @return Raw
     */
    public function execute()
    {
        $result = $this->resultRawFactory->create();
        if (!$this->settings->isEnabled()) {
            $result->setContents('Module is disabled.');
            return $result;
        }

        $code = $this->getRequest()->getParam('code');
        $storeCode = $this->getRequest()->getParam('store');

        if (!$this->profitHelper->checkSecretCode($code)) {
            $result->setContents('You are not authorized to request these data.');
            return $result;
        }

        try {
            $store = null;
            if ($storeCode) {
                $store = $this->storeManager->getStore($storeCode);
            }

            $feedFilePath = $this->profitHelper->getFeedFilePath($store);
            $feedContents = file_get_contents($feedFilePath);

            if (!$feedContents) {
                $result->setContents('No feed data');
                return $result;
            }

            $result->setHeader('Content-Type', 'text/xml');
            $result->setContents(file_get_contents($feedFilePath));
        } catch (Exception $exception) {
            $this->logger->critical($exception);
            $result->setContents('Error! File not found.');
        }

        return $result;
    }
}
