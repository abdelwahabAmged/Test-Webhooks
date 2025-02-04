<?php

namespace PWA\FrontendRedirect\Model\Router;

use PWA\FrontendRedirect\Model\Config;
use PWA\FrontendRedirect\Model\RouterInterface;

class CmsRouter implements RouterInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \PWA\Base\Helper\Url 
     */
    protected $urlHelper;

    /**
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \PWA\Base\Helper\Url $urlHelper
    ) {
        $this->config = $config;
        $this->_eventManager = $eventManager;
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->urlHelper = $urlHelper;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');

        $condition = new \Magento\Framework\DataObject(['identifier' => $identifier, 'continue' => true]);
        $this->_eventManager->dispatch(
            'cms_controller_router_match_before',
            ['router' => $this, 'condition' => $condition]
        );
        $identifier = $condition->getIdentifier();

        if ($condition->getRedirectUrl()) {
            return $this->resultRedirectFactory->create()
                ->setUrl($condition->getRedirectUrl());
        }

        if (!$condition->getContinue()) {
            return null;
        }

        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->_pageFactory->create();
        $pageId = $page->checkIdentifier($identifier, $this->_storeManager->getStore()->getId());
        if (!$pageId) {
            return null;
        }

        return $this->resultRedirectFactory->create()
            ->setUrl($this->urlHelper->concatUrl($this->config->getRedirectUrl(), $request->getPathInfo()));
    }
}
