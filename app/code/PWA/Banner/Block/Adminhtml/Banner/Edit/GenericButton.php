<?php

namespace PWA\Banner\Block\Adminhtml\Banner\Edit;

use Magento\Backend\Block\Widget\Context;
use PWA\Banner\Model\BannerImageFactory;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     * @param BannerImageFactory $bannerImageFactory
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Return banner ID
     *
     * @return int|null
     */
    public function getImageId()
    {
        return $this->context->getRequest()->getParam('image_id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
