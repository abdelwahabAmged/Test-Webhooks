<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

namespace DV\ProfitMetrics\Controller\Adminhtml\Feed;

use DV\ProfitMetrics\Model\Config\Settings;
use DV\ProfitMetrics\Model\ExportProductData;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Generate
 * @package DV\ProfitMetrics\Controller\Adminhtml\Feed
 */
class Generate extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'DV_ProfitMetrics::generate';

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ExportProductData
     */
    private $exportProductData;

    /**
     * Generate constructor.
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param Settings $settings
     * @param ExportProductData $exportProductData
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        Settings $settings,
        ExportProductData $exportProductData
    ) {
        parent::__construct($context);

        $this->resultForwardFactory = $resultForwardFactory;
        $this->settings = $settings;
        $this->exportProductData = $exportProductData;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($this->settings->isEnabled()) {
            $this->exportProductData->exportProductData();
        }

        return;
    }
}
