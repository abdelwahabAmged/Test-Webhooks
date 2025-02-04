<?php

/**
 * @category    Scandiweb
 * @author      Amr osama <amr.osama@scandiweb.com>
 * @author      Abdullah Ashraf <info@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Scandiweb\HyvaUi\ViewModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;

class OrderInfo implements ArgumentInterface
{
    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var ProductHelper
     */
    private ProductHelper $productHelper;

    /**
     * @var AddressRenderer
     */
    private AddressRenderer $addressRenderer;

    /**
     * @var Order
     */
    private Order $order;

    /**
     * @var ProductResource
     */
    private ProductResource $productResource;

    /**
     * @param Session $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param ProductHelper $productHelper
     * @param AddressRenderer $addressRenderer
     * @param ProductResource $productResource
     */
    public function __construct(
        Session $checkoutSession,
        ProductRepositoryInterface $productRepository,
        ProductHelper $productHelper,
        AddressRenderer $addressRenderer,
        ProductResource $productResource
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
        $this->addressRenderer = $addressRenderer;
        $this->productResource = $productResource;
        $this->prepareOrderData();
    }

    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareOrderData(): void
    {
        $this->order = $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Returns order object
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * Retrieves a product's thumbnail
     *
     * @param int $productId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductThumbnail(int $productId): string
    {
        try {
            $product = $this->productRepository->getById($productId);

            return $this->productHelper->getThumbnailUrl($product);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__("This product doesn't exist"));
        }
    }

    /**
     * Return formatted shipping address
     *
     * @param Address $address
     * @return string|null
     */
    public function getFormattedAddress(Address $address): string|null
    {
        return $this->addressRenderer->format($address, 'html');
    }

    /**
     * Retrieves product resource
     *
     * @return ProductResource
     */
    public function getProductResource(): ProductResource
    {
        return $this->productResource;
    }
}
