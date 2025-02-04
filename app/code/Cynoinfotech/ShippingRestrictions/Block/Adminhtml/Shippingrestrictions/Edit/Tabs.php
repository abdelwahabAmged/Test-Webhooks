<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Block\Adminhtml\Shippingrestrictions\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('shippingrestrictions_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Information'));
    }
    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'restrictions_info',
            [
                'label' => __('Shipping Method'),
                'title' => __('Shipping Method'),
                'content' => $this->getLayout()->createBlock(
                    'Cynoinfotech\ShippingRestrictions\Block\Adminhtml\Shippingrestrictions\Edit\Tab\Maintab'
                )->toHtml(),
                'active' => true
            ]
        );
      
        $this->addTab(
            'shippingrestrictions_conditions',
            [
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'content' => $this->getLayout()->createBlock(
                    'Cynoinfotech\ShippingRestrictions\Block\Adminhtml\Shippingrestrictions\Edit\Tab\Conditions'
                )->toHtml()
            ]
        );
        
        $this->addTab(
            'stores_and_customer_groups',
            [
                'label' => __('Stores & Customer Groups'),
                'title' => __('Stores & Customer Groups'),
                'content' => $this->getLayout()->createBlock(
                    'Cynoinfotech\ShippingRestrictions\Block\Adminhtml\Shippingrestrictions\Edit\Tab\CustomerGroups'
                )->toHtml()
            ]
        );
        
        $this->addTab(
            'days_info',
            [
                'label' => __('Days Info'),
                'title' => __('Days Info'),
                'content' => $this->getLayout()->createBlock(
                    'Cynoinfotech\ShippingRestrictions\Block\Adminhtml\Shippingrestrictions\Edit\Tab\DaysInfo'
                )->toHtml()
            ]
        );
        
        $this->addTab(
            'shippingrestrictions_coupons',
            [
                'label' => __('Coupons'),
                'title' => __('Coupons'),
                'content' => $this->getLayout()->createBlock(
                    'Cynoinfotech\ShippingRestrictions\Block\Adminhtml\Shippingrestrictions\Edit\Tab\CouponsCode'
                )->toHtml()
            ]
        );
        return parent::_beforeToHtml();
    }
}
