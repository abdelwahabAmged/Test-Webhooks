<?php

namespace PWA\FrontendRedirect\Model\Router;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\UrlInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use PWA\Base\Helper\Url;
use PWA\FrontendRedirect\Model\Config;
use PWA\FrontendRedirect\Model\RouterInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UrlRewriteRouter implements RouterInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Url
     */
    protected $urlHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param UrlFinderInterface $urlFinder
     */
    public function __construct(
        Config $config,
        \PWA\Base\Helper\Url $urlHelper,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        UrlFinderInterface $urlFinder,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->config = $config;
        $this->urlHelper = $urlHelper;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->urlFinder = $urlFinder;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $rewrite = $this->getRewrite(
            $request->getPathInfo(),
            $this->storeManager->getStore()->getId()
        );

        if ($rewrite === null) {
            //No rewrite rule matching current URl found, continuing with
            //processing of this URL.
            return null;
        }
        if ($rewrite->getRedirectType()) {
            //Rule requires the request to be redirected to another URL
            //and cannot be processed further.
            return $this->processRedirect($request, $rewrite);
        }
        //Rule provides actual URL that can be processed by a controller.
        if ($rewrite->getEntityType() == 'product') {
            return $this->processProductRewrite($rewrite);
        } else if ($rewrite->getEntityType() == 'category') {
            return $this->processCategoryRewrite($rewrite);
        }
        return null;
    }

    /**
     * @param UrlRewrite $rewrite
     * @return \Magento\Framework\Controller\Result\Redirect|null
     */
    protected function processProductRewrite(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite $rewrite)
    {
        try {
            $product = $this->productRepository->getById($rewrite->getEntityId(), false, $rewrite->getStoreId());
            $url = $this->config->getRedirectUrl() . $this->urlHelper->getProductPath($product);
            return $this->resultRedirectFactory->create()
                ->setUrl($url);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param UrlRewrite $rewrite
     * @return \Magento\Framework\Controller\Result\Redirect|null
     */
    protected function processCategoryRewrite(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite $rewrite)
    {
        try {
            $category = $this->categoryRepository->get($rewrite->getEntityId(), $rewrite->getStoreId());
            $url = $this->config->getRedirectUrl() . $this->urlHelper->getCategoryPath($category);
            return $this->resultRedirectFactory->create()
                ->setUrl($url);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Process redirect
     *
     * @param RequestInterface $request
     * @param UrlRewrite $rewrite
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function processRedirect($request, $rewrite)
    {
        $target = $rewrite->getTargetPath();
        if ($rewrite->getEntityType() !== Rewrite::ENTITY_TYPE_CUSTOM
            || ($prefix = substr($target, 0, 6)) !== 'http:/' && $prefix !== 'https:'
        ) {
            $target = $this->url->getUrl('', ['_direct' => $target]);
        }
        return $this->redirect($request, $target, $rewrite->getRedirectType());
    }

    /**
     * Redirect to target URL
     *
     * @param RequestInterface|HttpRequest $request
     * @param string $url
     * @param int $code
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function redirect($request, $url, $code)
    {
        return $this->resultRedirectFactory->create()
            ->setUrl($url)->setHttpResponseCode($code);
    }

    /**
     * Find rewrite based on request data
     *
     * @param string $requestPath
     * @param int $storeId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    protected function getRewrite($requestPath, $storeId)
    {
        return $this->urlFinder->findOneByData(
            [
                UrlRewrite::REQUEST_PATH => ltrim($requestPath, '/'),
                UrlRewrite::STORE_ID => $storeId,
            ]
        );
    }
}
