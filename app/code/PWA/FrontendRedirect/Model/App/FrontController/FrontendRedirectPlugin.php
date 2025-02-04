<?php

namespace PWA\FrontendRedirect\Model\App\FrontController;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use PWA\FrontendRedirect\Model\Config;
use Magento\Framework\Session\SessionManager;
use PWA\FrontendRedirect\Model\RouterInterface;

class FrontendRedirectPlugin
{
    const DISABLE_PARAM_NAME = 'disable_frontend_redirect';
    const ENABLE_PARAM_NAME = 'enable_frontend_redirect';

    const SESSION_PARAM_REDIRECT_DISABLED = 'frontend_redirect_disabled';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var SessionManager
     */
    protected $sessionManger;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var RouterInterface[]
     */
    protected $routers;

    public function __construct(
        Config $config,
        SessionManager $sessionManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        array $routers = []
    ) {
        $this->config = $config;
        $this->sessionManger = $sessionManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->routers = $routers;
    }

    /**
     * @param \Magento\Framework\App\FrontControllerInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\Response\Http
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\FrontControllerInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if ($this->config->getEnable() && $this->shouldRedirect($request)) {
            foreach ($this->routers as $router) {
                $result = $router->match($request);
                if ($result) {
                    break;
                }
            }
            if (!$result) {
                $result = $this->resultRedirectFactory->create()
                    ->setUrl($this->config->getRedirectUrl());
            }
            $result->setHeader('Cache-Control', '');
        } else {
            $result = $proceed($request);
        }
        return $result;
    }

    protected function shouldRedirect(Http $request)
    {
        return !$this->isTemporaryDisabled($request) && !$this->isPathIgnored($request);
    }

    protected function isPathIgnored(Http $request)
    {
        $path = $request->getPathInfo();
        if ($path == '') {
            $path = '/';
        } else {
            $path = strtolower($path);
        }

        foreach ($this->config->getIgnorePaths() as $ignorePath) {
            if (strpos($path, strtolower($ignorePath)) === 0) {
                return true;
            }
        }

        return false;
    }

    protected function isTemporaryDisabled(Http $request)
    {
        $param = $this->config->getDisableParameter();
        if (empty($param)) {
            return false;
        }

        $this->processDisableParam($request, $param);

        return (bool)$this->sessionManger->getData(self::SESSION_PARAM_REDIRECT_DISABLED);
    }

    protected function processDisableParam(Http $request, $param)
    {
        $disableParam = $request->getParam(self::DISABLE_PARAM_NAME);
        if ($disableParam && $disableParam == $param) {
            $this->sessionManger->setData(self::SESSION_PARAM_REDIRECT_DISABLED, true);
            return;
        }

        $enableParam = $request->getParam(self::ENABLE_PARAM_NAME);
        if ($enableParam && $enableParam == $param) {
            $this->sessionManger->unsetData(self::SESSION_PARAM_REDIRECT_DISABLED);
        }
    }
}
