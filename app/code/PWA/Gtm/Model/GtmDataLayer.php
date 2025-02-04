<?php

namespace PWA\Gtm\Model;

use Magento\Sales\Model\Order;
use PWA\Gtm\Api\GtmDataLayerInterface;
use MagePal\GoogleTagManager\Model\Order as GtmOrderDataArray;

class GtmDataLayer implements GtmDataLayerInterface
{
    /**
     * @var GtmOrderDataArray
     */
    protected $gtmOrderDataArray;

    public function __construct(GtmOrderDataArray $gtmOrderDataArray)
    {
        $this->gtmOrderDataArray = $gtmOrderDataArray;
    }

    public function getOrderDataLayer(Order $order)
    {
        $result = [];
        $data = [
            'ecommerce' => [
                'currencyCode' => $order->getOrderCurrencyCode(),
                'purchase' => [
                    'actionField' => [
                        'revenue' => $order->getGrandTotal(), // dlv - revenue
                        'id' => $order->getIncrementId(), // dlv - Order-id
                        'tax' => $order->getTaxAmount(), // dlv - transactionTax
                        'shipping' => $order->getShippingInclTax(), // dlv - shipping
                    ]
                ]
            ],
            'list' => 'other',
            'pageType' => 'takfordinordre'
        ];
        if ($order->getCouponCode() != '') {
            $data['ecommerce']['purchase']['actionField']['coupon'] = $order->getCouponCode(); // dlv - coupon
        }
        $result[] = $data;

        $transactions = $this->gtmOrderDataArray->setOrderIds([$order->getId()])->getOrderLayer();

        $result = array_merge($result, $transactions);

        return $result;
    }
}
