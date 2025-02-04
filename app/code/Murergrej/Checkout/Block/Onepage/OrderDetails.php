<?php

/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @developer   Abanoub Youssef <info@scandiweb.com>
 *
 * This block class provides order details for the order success page.
 */
declare(strict_types=1);

namespace Murergrej\Checkout\Block\Onepage;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;

class OrderDetails extends Template
{
    /**
     * @var CheckoutSession $checkoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * @var ImageHelper $imageHelper
     */
    protected ImageHelper $imageHelper;

    /**
     * @var CatalogHelper $catalogHelper
     */
    public CatalogHelper $catalogHelper;

    /**
     * @var TaxHelper $taxHelper
     */
    protected TaxHelper $taxHelper;

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param ImageHelper $imageHelper
     * @param CatalogHelper $catalogHelper
     * @param TaxHelper $taxHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        ImageHelper $imageHelper,
        CatalogHelper $catalogHelper,
        TaxHelper $taxHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->imageHelper = $imageHelper;
        $this->catalogHelper = $catalogHelper;
        $this->taxHelper = $taxHelper;
    }

    /**
     * Get order details
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Get product image URL
     *
     * @param Product $product
     * @return string
     */
    public function getProductImageUrl(Product $product): string
    {
        return $this->imageHelper->init($product, 'cart_page_product_thumbnail')->getUrl();
    }
}
