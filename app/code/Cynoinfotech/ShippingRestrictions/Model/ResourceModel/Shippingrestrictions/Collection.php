<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Model\ResourceModel\Shippingrestrictions;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    protected $_idFieldName = 'sr_id';
   
    protected function _construct()
    {
        $this->_init(
            'Cynoinfotech\ShippingRestrictions\Model\Shippingrestrictions',
            'Cynoinfotech\ShippingRestrictions\Model\ResourceModel\Shippingrestrictions'
        );
    }
}
