<?php

namespace PWA\FrontendRedirect\Model\Router;

use PWA\FrontendRedirect\Model\Config;
use PWA\FrontendRedirect\Model\RouterInterface;

class CustomRedirectsRouter implements RouterInterface
{
    /**
     * @var \PWA\Base\Helper\Url
     */
    protected $urlHelper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    public function __construct(
        \PWA\Base\Helper\Url $urlHelper,
        Config $config,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->urlHelper = $urlHelper;
        $this->config = $config;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $path = rtrim($request->getPathInfo(), '/');
        foreach ($this->config->getCustomRedirects() as $redirect) {
            $from = rtrim($redirect[0], '/');
            if ($from == $path) {
                return $this->resultRedirectFactory->create()
                    ->setUrl($this->urlHelper->concatUrl($this->config->getRedirectUrl(), $redirect[1]));
            }
        }
        return null;
    }
}
