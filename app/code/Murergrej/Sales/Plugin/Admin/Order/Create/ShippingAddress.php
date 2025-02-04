<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SmsNotification
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Murergrej\Sales\Plugin\Admin\Order\Create;

class ShippingAddress
{
    /**
     *
     * @return array
     */
    public function afterGetFormValues(\Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Address $subject, $data)
    {
        $customer = $subject->getCreateOrderModel()->getQuote()->getCustomer();
        if (empty($data['vat_id'])) {
            $data['vat_id'] = $customer->getTaxvat();
        }
        return $data;
    }
}
