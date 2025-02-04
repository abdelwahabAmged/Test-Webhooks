<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Model;

use Magento\Quote\Model\Quote\Address;
use Magento\Rule\Model\AbstractModel;

class Shippingrestrictions extends \Magento\SalesRule\Model\Rule
{     
    const STATUS_ENABLED = 1;
	
    const STATUS_DISABLED = 0;
        
    protected function _construct()
    {
        $this->_init('Cynoinfotech\ShippingRestrictions\Model\ResourceModel\Shippingrestrictions');
    }    
    
}