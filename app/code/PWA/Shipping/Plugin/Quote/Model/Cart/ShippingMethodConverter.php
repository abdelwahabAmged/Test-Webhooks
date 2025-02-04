<?php

namespace PWA\Shipping\Plugin\Quote\Model\Cart;

class ShippingMethodConverter
{
    /**
     * @param \Magento\Quote\Model\Cart\ShippingMethodConverter $subject
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface $result
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface
     */
    public function afterModelToDataObject(\Magento\Quote\Model\Cart\ShippingMethodConverter $subject, $result, $rateModel)
    {
        if ($result->getCarrierCode() == 'vconnectpostnord') {
            $vcData = $rateModel->getData('vc_method_data');
            if ($vcData) {
                $vcData = json_decode($vcData, true);
            }
            if ($vcData && isset($vcData['delivery_time'])) {
                $result->getExtensionAttributes()->setTransitTime($vcData['delivery_time']);
            }
        }
        return $result;
    }
}
