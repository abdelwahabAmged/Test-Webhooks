<?php
/**
 * @category    Murergrej
 * @package     Murergrej_HelloRetail
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\HelloRetail\Block;

use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template;
use Murergrej\HelloRetail\Service\HelloRetailService;

/**
 * Class PopularProducts
 */
class HelloRetailProducts extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = "Murergrej_HelloRetail::hello_retail_products_block.phtml";

    /**
     * @param Template\Context $context
     * @param HelloRetailService $helloRetailService
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        protected HelloRetailService $helloRetailService,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getRecommendationKey()
    {
        return $this->getData('recommendation_key');
    }

    /**
     * @return array
     */
    public function getRecommendedProducts(): array
    {
        return $this->helloRetailService->getRecommendedProducts($this->getRecommendationKey());
    }
}
