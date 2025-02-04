<?php

namespace PWA\Tracking\Model;

class Info extends \Magento\Shipping\Model\Info
{
    /**
     * Retrieve tracking by tracking entity id
     *
     * @return array
     */
    public function getTrackingInfoByTrackId()
    {
        /** @var \Magento\Shipping\Model\Order\Track $track */
        $track = $this->_trackFactory->create()->load($this->getTrackId());
        if ($track->getId() && $this->getProtectCode() === $track->getProtectCode()) {
            $shipment = $this->shipmentRepository->get($track->getParentId());
            $this->_trackingInfo = [$shipment->getIncrementId() => [$track->getNumberDetail()]];
        }
        return $this->_trackingInfo;
    }
}
