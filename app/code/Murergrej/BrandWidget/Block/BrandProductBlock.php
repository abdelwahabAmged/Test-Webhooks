<?php
/**
 * @category    Murergrej
 * @package     Murergrej_BrandWidget
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\BrandWidget\Block;

use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template;
use Murergrej\HelloRetail\Service\HelloRetailService;

/**
 * Class BrandProductBlock
 */
class BrandProductBlock extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = "Murergrej_BrandWidget::brand_product_block.phtml";

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

    public function getDesktopHeroImage()
    {
        return $this->getData('desktop_hero_image');
    }

    public function getMobileHeroImage()
    {
        return $this->getData('mobile_hero_image');
    }

    public function getDesktopBrandImage()
    {
        return $this->getData('desktop_brand_image');
    }

    public function getMobileBrandImage()
    {
        return $this->getData('mobile_brand_image');
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
