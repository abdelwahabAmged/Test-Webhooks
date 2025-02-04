<?php

namespace PWA\Tracking\Model;

use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Model\Order;
use PWA\Tracking\Api\GuestTrackingProviderInterface;

class TrackingProvider implements GuestTrackingProviderInterface
{
    protected $shippingInfoFactory;

    public function __construct(
        \PWA\Tracking\Model\InfoFactory $shippingInfoFactory
    ) {
        $this->shippingInfoFactory = $shippingInfoFactory;
    }

    /**
     * @param Order $order
     * @return array|mixed
     */
    public function getTrackingInformation($order)
    {
        /** @var \Magento\Shipping\Model\Info $shippingInfo */
        $shippingInfo = $this->shippingInfoFactory->create();
        $shippingInfo->setData('order_id', $order->getId());
        $shippingInfo->setData('protect_code', $order->getProtectCode());

        return $this->prepareInfo($shippingInfo->getTrackingInfoByOrder());
    }

    /**
     * @param string $hash
     * @return array|mixed
     */
    public function getByHash($hash)
    {
        /** @var \Magento\Shipping\Model\Info $shippingInfo */
        $shippingInfo = $this->shippingInfoFactory->create();
        $shippingInfo->loadByHash($hash);

        return $this->prepareInfo($shippingInfo->getTrackingInfo());
    }

    protected function prepareInfo($info)
    {
        if (empty($info)) {
            throw new NotFoundException(__('Page not found.'));
        }
        $result = [];
        foreach ($info as $shipmentId => $tracks) {
            $result[] = [
                'shipment' => $shipmentId,
                'tracks' => array_map(function ($item) {
                    if (is_object($item)) {
                        return $item->getData();
                    } else {
                        return $item;
                    }
                }, $tracks)
            ];
        }

        return $result;
    }
}
