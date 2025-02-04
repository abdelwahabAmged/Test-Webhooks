<?php
/**
 * Order Summary Block
 *
 * This block is responsible for fetching the order summary data to be displayed on the order success page.
 *
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @developer   Abanoub Youssef <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Murergrej\Checkout\Block\Onepage;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Escaper;
use Magento\Directory\Model\CountryFactory;

class OrderSummary extends Template
{
    /**
     * Tax rate for pallets.
     */
    public const PALLETS_TAX = 0.25;

    /**
     * @var CountryFactory
     */
    private CountryFactory $countryFactory;
    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param Escaper $escaper
     * @param CountryFactory $countryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        Escaper $escaper,
        CountryFactory $countryFactory,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->countryFactory = $countryFactory;
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * Get the last order data.
     *
     * @return array|null
     */
    public function getOrderData(): ?array
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if (!$order || !$order->getEntityId()) {
            return null;
        }

        $shippingAddress = $order->getShippingAddress();

        // Prepare data for the template
        return [
            'subtotal' => (float)$order->getSubtotal(),
            'shipping' => (float)$order->getShippingInclTax(),
            'palletCost' => (float)$order->getPalletCost(),
            'tax' => (float)$order->getTaxAmount(),
            'grandTotal' => (float)$order->getGrandTotal() - (float)$order->getPalletCost() * self::PALLETS_TAX,
            'palletCount' => (float)$order->getPalletCount(),
            'name' => $shippingAddress
                ? $this->escaper->escapeHtml($shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname())
                : 'N/A',
            'address' => $shippingAddress
                ? $this->escaper->escapeHtml(
                    implode(', ', array_filter([
                        implode(', ', array_filter($shippingAddress->getStreet())),
                        $shippingAddress->getRegion(),
                        $shippingAddress->getCity(),
                        $this->getCountryName($shippingAddress->getCountryId()),
                        $shippingAddress->getPostcode()
                    ]))
                )
                : 'N/A'
        ];
    }

    /**
     * Get country name by country ID.
     *
     * @param string $countryId
     * @return string
     */
    private function getCountryName(string $countryId): string
    {
        return $this->countryFactory->create()->loadByCode($countryId)->getName();
    }
}
