<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Block\Adminhtml\Shippingrestrictions;
 
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
 
class Edit extends Container
{
   /**
    * Core registry
    * @var \Magento\Framework\Registry
    */
    protected $coreRegistry = null;
 
    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }
 
    /**
     * Class constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'sr_id';
        $this->_controller = 'adminhtml_shippingrestrictions';
        $this->_blockGroup = 'Cynoinfotech_ShippingRestrictions';
        parent::_construct();
        
        $this->buttonList->update('save', 'label', __('Save'));
        
        if ($this->_isAllowedAction('Cynoinfotech_ShippingRestrictions::delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Preferences'));
        } else {
            $this->buttonList->remove('delete');
        }
        
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
    }
    
    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
 
    /**
     * Retrieve text for header element depending on loaded faqs
     *
     * @return string
     */
    public function getHeaderText()
    {
        $coreRegistryData = $this->coreRegistry->registry('shippingrestrictions');
        if ($coreRegistryData->getSrId()) {
            $name = $this->escapeHtml($coreRegistryData->getName());
            return __("Edit shipping Restrictions's '%1'", $name);
        } else {
            return __("Add shipping Restrictions's");
        }
    }
 
    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('post_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            };
        ";
 
        return parent::_prepareLayout();
    }
}
