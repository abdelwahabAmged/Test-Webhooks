<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Model;

use Magento\Sales\Model\Order;
use Magento\Framework\HTTP\LaminasClientFactory;
use Laminas\Http\Exception\RuntimeException;
use Laminas\Http\Request;
use DV\ProfitMetrics\Model\Config\Settings;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\Helper\Data;

class ProfitMetricsApiOrdersSender
{
    const VERSION = '2';

    /**
     * @var \DV\ProfitMetrics\Model\Config\Settings
     */
    private Settings $settings;

    /**
     * @var LaminasClientFactory
     */
    private LaminasClientFactory $httpClientFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private Json $jsonSerializer;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private Data $urlHelper;

    /**
     * @param Settings $settings
     * @param LaminasClientFactory $httpClientFactory
     * @param Json $jsonSerializer
     * @param Data $urlHelper
     */
    public function __construct(
        Settings $settings,
        LaminasClientFactory $httpClientFactory,
        Json $jsonSerializer,
        Data $urlHelper
    ) {
        $this->settings = $settings;
        $this->httpClientFactory = $httpClientFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param Order $order
     * @return string|null
     */
    public function sendOrderData(Order $order): ?string
    {
        $client = $this->httpClientFactory->create();
        $profitMetricsApiUrl = $this->getProfitMetricsApiUrl();
        $parameters = [
            'v' => self::VERSION,
            'pid' => $this->settings->getProfitMetricsId($order->getStore()),
            'gacid' => $order->getGacid(),
            'gacid_source' => $order->getGacidSource(),
            'gclid' => $order->getGclid(),
            'fbp' => $order->getFbp(),
            'fbc' => $order->getFbc(),
            'cua' => urlencode((string) $order->getCua()),
            'cip' => $order->getCip(),
            'o' => $this->getOrderSpecification($order),
            't' => urlencode((string)$order->getT()),
        ];
        $parameters = array_filter($parameters, static function ($value) {
            return (string) $value !== '';
        });
        $url = $this->urlHelper->addRequestParam($profitMetricsApiUrl, $parameters);
        $client->setUri($url);
        $client->setMethod(Request::METHOD_GET);

        try {
            $response = $client->send();
        } catch (RuntimeException $exception) {
            $error = $client->getAdapter()->getError();
            throw new RuntimeException('Error making API query to profitmetrics, details: ' . $error);
        }

        return $response->getBody();
    }

    /**
     * @return string
     */
    private function getProfitMetricsApiUrl(): string
    {
        return 'https://my.profitmetrics.io/l.php';
        // test server:
        // return 'https://testinst1.int.profitmetrics.io/l.php';
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getOrderSpecification(Order $order): string
    {
        return urlencode($this->getOrderDataJson($order));
    }

    /**
     * @param Order $order
     * @return bool|string
     */
    public function getOrderDataJson(Order $order)
    {
        if (!$order || !$order->getId()) {
            return '';
        }

        return $this->jsonSerializer->serialize($this->getOrderData($order));
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getOrderData(Order $order): array
    {
        if (!$order->getId()) {
            return [];
        }

        $itemsData = [];
        $simpleProductIdsByOrderItemId = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllItems() as $item) {
            // skipping simple items which has configurable parent ones
            /** @var \Magento\Sales\Model\Order\Item $parentItem */
            if (
                ($parentItem = $item->getParentItem())
                && ($product = $parentItem->getProduct())
                && ($product->getTypeId() === 'configurable')
            ) {
                $simpleProductIdsByOrderItemId[$parentItem->getId()] = $item->getProductId();
                continue;
            }

            if (($item->getProductType() === 'bundle')) {
                continue;
            }

            $itemsData[] = array(
                'orderItemId' => $item->getId(),
                'sku' => $item->getProductId(),
                'qty' => (int)$item->getQtyOrdered(),
                'priceExVat' => (double) sprintf('%5.2f', $item->getPrice())
            );
        }

        foreach ($itemsData as $key => $item) {
            $orderItemId = $item['orderItemId'];
            $productId = $simpleProductIdsByOrderItemId[$orderItemId] ?? $item['sku'];

            $itemsData[$key]['sku'] = (string) $productId;
            unset($itemsData[$key]['orderItemId']);
        }

        return array(
            'id' => (string) $order->getIncrementId(),
            'orderEmail' => $order->getCustomerEmail(),
            'currency' => $order->getStore()->getCurrentCurrencyCode(),
            'priceShippingExVat' => (float)$order->getShippingAmount(),
            'priceTotalExVat' => (float)sprintf('%5.2f', $order->getGrandTotal() - $order->getTaxAmount()),
            'paymentMethod' => $order->getPayment()->getMethod(),
            'shippingMethod' => $order->getShippingMethod(),
            'products' => $itemsData
        );
    }
}
