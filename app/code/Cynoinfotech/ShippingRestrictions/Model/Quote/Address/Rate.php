<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Model\Quote\Address;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ObjectManager;

class Rate extends \Magento\Quote\Model\Quote\Address\Rate
{
    /**
     * @var \Magento\Quote\Model\Quote\Address
     */
    protected $_address;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Quote\Model\ResourceModel\Quote\Address\Rate');
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        if ($this->getAddress()) {
            $this->setAddressId($this->getAddress()->getId());
        }
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    public function setAddress(\Magento\Quote\Model\Quote\Address $address)
    {
        $this->_address = $address;
        return $this;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate
     * @return $this
     */
    public function importShippingRate(\Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate)
    {
        $helper = ObjectManager::getInstance()->create('Cynoinfotech\ShippingRestrictions\Helper\Data');		
		$httpRequest = ObjectManager::getInstance()->get('\Magento\Framework\App\Request\Http');	
        
        if (($helper->getConfig('cynoinfotech_shippingrestrictions/general/active')) AND $httpRequest->getFullActionName() != 'multishipping_checkout_addressesPost' ) {
		
            $finalRestrictedArray = [];
            $restrictedRules = $helper->getRestrictedShippingMethods();
            
            //---------------- if enalbe disply error if not then run else if ---------------//

            if ($helper->getConfig('cynoinfotech_shippingrestrictions/general/display_error')) {
                if (in_array($rate->getCarrier().'_'. $rate->getMethod(), array_keys($restrictedRules))) {
                        $this->setCode(
                            $rate->getCarrier() . '_error'
                        )->setCarrier(
                            $rate->getCarrier()
                        )->setCarrierTitle(
                            $rate->getCarrierTitle()
                        )->setErrorMessage(
                            $restrictedRules[$rate->getCarrier().'_'. $rate->getMethod()]
                        );
                    return $this;
                } elseif (in_array($rate->getCarrier().'_'. $rate->getMethod(), array_keys($restrictedRules))) {
                    return $this;
                }
            } elseif (in_array($rate->getCarrier().'_'. $rate->getMethod(), array_keys($restrictedRules))) {
                return $this;
            }
        }
        //-------------------------------- core code ---------------------------------------//
        
        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
            $this->setCode(
                $rate->getCarrier() . '_error'
            )->setCarrier(
                $rate->getCarrier()
            )->setCarrierTitle(
                $rate->getCarrierTitle()
            )->setErrorMessage(
                $rate->getErrorMessage()
            );
        } elseif ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            $this->setCode(
                $rate->getCarrier() . '_' . $rate->getMethod()
            )->setCarrier(
                $rate->getCarrier()
            )->setCarrierTitle(
                $rate->getCarrierTitle()
            )->setMethod(
                $rate->getMethod()
            )->setMethodTitle(
                $rate->getMethodTitle()
            )->setMethodDescription(
                $rate->getMethodDescription()
            )->setPrice(
                $rate->getPrice()
            );
        }
        return $this;
    }
}
