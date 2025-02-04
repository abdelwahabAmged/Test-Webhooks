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

class CustomerGroups extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;
       
    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerGroup;
 
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
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerCollection,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->customerGroup = $customerCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }
 
    /**
     * Prepare form fields
     * @return \Magento\Backend\Block\Widget\Form
     */
     
    protected function _prepareForm()
    {
       /**
       * @var $model \Cynoinfotech\ShippingRestrictions\Model\Shippingrestrictions
       */
        $model = $this->_coreRegistry->registry('shippingrestrictions');
        if ($this->_isAllowedAction('Cynoinfotech_ShippingRestrictions::save')) {
            $isElementDisabled = 0;
        } else {
            $isElementDisabled = 1;
        }
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('shippingrestrictions_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Apply In')]);
        if ($model->getId()) {
            $fieldset->addField('sr_id', 'hidden', ['name' => 'sr_id']);
        }
        
        /**
         * Check is single store mode
         */
         
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'values' => $this->systemStore->getStoreValuesForForm(false, true),
                    'required' => true,
                    'disabled' => $isElementDisabled,
                    
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'stores',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }
        
        /*----------------------------------------------------------*/
        
        $fieldset = $form->addFieldset(
            'apply_for',
            [
                'legend' => __('Apply For')
             ]
        );
        
        $groupOptions = $this->customerGroup->toOptionArray();
        $fieldset->addField(
            'customer_group',
            'multiselect',
            [
                'name'     => 'customer_group[]',
                'label'    => __('Customer Groups'),
                'title' => __('Customer Group'),
                'values' => $groupOptions,
                'required' => true,
                'disabled' => $isElementDisabled,
            ]
        );
        
        /*----------------------------------------------------------*/
        
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     * @return string
     */
    public function getTabLabel()
    {
        return __("Stores And Customer Groups");
    }

    /**
     * Prepare title for tab
     * @return string
     */
    public function getTabTitle()
    {
        return __("Stores And Customer Groups");
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
