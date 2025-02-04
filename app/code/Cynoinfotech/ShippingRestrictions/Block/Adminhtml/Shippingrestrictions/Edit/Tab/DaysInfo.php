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

class DaysInfo extends Generic implements TabInterface
{
   
    /**
     * @var \Cynoinfotech\ShippingRestrictions\Model\Source\Days
     */
    protected $days;

   /**
    * @param Context $context
    * @param Registry $registry
    * @param FormFactory $formFactory
    * @param Status $shippingrestrictStatus
    * @param array $data
    */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        \Cynoinfotech\ShippingRestrictions\Model\Source\Days $days,
        array $data = []
    ) {
        $this->days = $days;
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
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Shipping Restrictions Days')]);
        
        if ($model->getId()) {
            $fieldset->addField('sr_id', 'hidden', ['name' => 'sr_id']);
        }
        
        $daysOptions = $this->days->toOptionArray();
        
        $fieldset->addField(
            'days',
            'multiselect',
            [
                'name'     => 'days[]',
                'label'    => __('Days of the week'),
                'title' => __('Days of the week'),
                'values' => $daysOptions,
                'disabled' => $isElementDisabled,
                'required' => true,
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
        return __("Days Info");
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __("Days Info");
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
