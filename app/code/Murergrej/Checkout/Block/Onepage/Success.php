<?php

/**
 *  Custom functionality for the success block in the checkout process.
 *
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Abanoub youssef <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\Checkout\Block\Onepage;

use Magento\Checkout\Block\Onepage\Success as MagentoSuccess;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Murergrej\HelloRetail\Service\HelloRetailService;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Framework\App\Http\Context as HttpContext;

class Success extends MagentoSuccess
{
    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param Config $orderConfig
     * @param HttpContext $httpContext
     * @param HelloRetailService $helloRetailService
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $orderConfig,
        HttpContext $httpContext,
        protected HelloRetailService $helloRetailService,
        protected ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }

    /**
     * Prepares block data with additional email address
     *
     * @return void
     */
    protected function prepareBlockData(): void
    {
        $order = $this->_checkoutSession->getLastRealOrder();

        $this->addData(
            [
                'is_order_visible' => $this->isVisible($order),
                'view_order_url' => $this->getUrl(
                    'sales/order/view/',
                    ['order_id' => $order->getEntityId()]
                ),
                'print_url' => $this->getUrl(
                    'sales/order/print',
                    ['order_id' => $order->getEntityId()]
                ),
                'can_print_order' => $this->isVisible($order),
                'can_view_order'  => $this->canViewOrder($order),
                'order_id'  => $order->getIncrementId(),
                'order_email' => $order->getCustomerEmail()
            ]
        );
    }

    /**
     * Get registration block html
     *
     * @return string
     */
    public function getRegistrationHtml(): string
    {
        return $this->getChildHtml('checkout.registration');
    }

    /**
     * @return $this|Success
     * @throws LocalizedException
     */
    protected function _prepareLayout(): Success|static
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->getUrl('')
            ]);
            $breadcrumbs->addCrumb('success', [
                'label' => __('Success Purchase'),
                'title' => __('Success Purchase')
            ]);
        }
        return $this;
    }

    /**
     * Returns recommendations key needed to fetch product SKUs from Hello Retail
     *
     * @return string
     */
    public function getHelloRetailRecommendationsKey(): string
    {
        return (string) $this->_scopeConfig->getValue(
            HelloRetailService::XML_PATH_SUCCESS_PAGE_HELLO_RETAIL_RECOMMENDATIONS_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getRecommendedProducts(): array
    {
        return $this->helloRetailService->getRecommendedProducts($this->getHelloRetailRecommendationsKey());
    }
}
