<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Block\Adminhtml\Shippingrestrictions\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class Maintab extends Generic implements TabInterface
{
    /**
     * @var \Cynoinfotech\ShippingRestrictions\Model\Source\GetAllshippingMethods
     */
    protected $gasm;
    
   /**
    * @param Context $context
    * @param Registry $registry
    * @param FormFactory $formFactory
    * @param array $data
    */
     
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        \Cynoinfotech\ShippingRestrictions\Model\Shippingrestrictions $Shippingrestrictions,
        \Magento\Shipping\Model\Config\Source\Allmethods $getAllshippingMethods,
        array $data = []
    ) {
        $this->Shippingrestrictions = $Shippingrestrictions;
        $this->gasm = $getAllshippingMethods;
        parent::__construct($context, $registry, $formFactory, $data);
    }
 
    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('shippingrestrictions');
        if ($this->_isAllowedAction('Cynoinfotech_ShippingRestrictions::save')) {
            $isElementDisabled = 0;
        } else {
            $isElementDisabled = 1;
        }
 
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('shippingrestrictions_');
        
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Shipping Restrictions Information')]);
                
        if ($model->getId()) {
            $fieldset->addField('sr_id', 'hidden', ['name' => 'sr_id']);
        }
        
        $fieldset->addField(
            'name',
            'text',
            [
                'name'     => 'name',
                'label'    => __('Name'),
                'title'    => __('Name'),
                'required' => true,
                'disabled' => $isElementDisabled,
            ]
        );
        
        $carrierOptions = $this->gasm->toOptionArray();
        $fieldset->addField(
            'carriers',
            'multiselect',
            [
                'name'     => 'carriers[]',
                'label'    => __('Shipping Carriers'),
                'title' => __('Shipping Carriers'),
                'required' => true,
                'values' => $carrierOptions,
                'disabled' => $isElementDisabled,
                'note'  => 'Select Multiple Shipping Carriers (Methods)',
            ]
        );
                
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name'     => 'sort_order',
                'label'    => __('Priority'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name'     => 'status',
                'label'    => __('Status'),
                'options'  => ["0" => "Disable","1" => "Enable"],
                'disabled' => $isElementDisabled
            ]
        );
        
        $fieldset->addField(
            'error_msg',
            'textarea',
            [
                'name'     => 'error_msg',
                'label'    => __('Error Message'),
                'disabled' => $isElementDisabled,
                'note'  => 'Add error comment that you want to display front side',
            ]
        );
        
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }
        
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __("Maintab Tabs Info");
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __("Maintab Tabs Info");
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
    
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
