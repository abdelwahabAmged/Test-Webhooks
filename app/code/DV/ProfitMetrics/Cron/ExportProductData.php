<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

namespace DV\ProfitMetrics\Cron;

use DV\ProfitMetrics\Model\Config\Settings;
use DV\ProfitMetrics\Model\ExportProductData as ExportProductDataModel;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ExportProductData
 * @package DV\ProfitMetrics\Cron
 */
class ExportProductData
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ExportProductDataModel
     */
    private $exportProductData;

    /**
     * ExportProductData constructor.
     * @param LoggerInterface $logger
     * @param Settings $settings
     * @param ExportProductDataModel $exportProductData
     */
    public function __construct(
        LoggerInterface $logger,
        Settings $settings,
        ExportProductDataModel $exportProductData
    ) {
        $this->logger = $logger;
        $this->settings = $settings;
        $this->exportProductData = $exportProductData;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $this->logger->info('Running cron from profitMetrics export product data');

        if (!$this->settings->isEnabled()) {
            $this->logger->info('ProfitMetrics module is disabled');
            return $this;
        }

        $this->exportProductData->exportProductData();

        $this->logger->info('Finish cron job from profitMetrics export product data');

        return $this;
    }
}
