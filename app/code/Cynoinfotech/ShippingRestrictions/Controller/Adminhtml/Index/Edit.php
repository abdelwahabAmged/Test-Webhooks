<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Cynoinfotech\ShippingRestrictions\Model\ShippingrestrictionsFactory;
use Magento\Backend\Model\Session;

class Edit extends \Magento\Backend\App\Action
{
    protected $_coreRegistry = null;

    protected $resultPageFactory;
    
    protected $session;

    protected $ShippingrestrictionsFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        Session $session,
        ShippingrestrictionsFactory $ShippingrestrictionsFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->session = $session;
        $this->ShippingrestrictionsFactory = $ShippingrestrictionsFactory;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cynoinfotech_ShippingRestrictions::save');
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cynoinfotech_ShippingRestrictions::Shippingrestrictions')
            ->addBreadcrumb(__('Shippingrestrictions'), __('Shippingrestrictions'))
            ->addBreadcrumb(__('Add Shipping Restrictions Rule'), __('Add Shipping Restriction Rule'));
        return $resultPage;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->ShippingrestrictionsFactory->create();
        
        if ($id) {
            $model->load($id);
            
            if (!$model->getSrId()) {
                $this->messageManager->addError(__('This Shipping Restriction Rule no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('shippingrestrictions', $model);
        $resultPage = $this->_initAction();
        
        $resultPage->addBreadcrumb(
            $id ? __('Edit Page') : __('Select'),
            $id ? __('Edit Page') : __('Select')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('shippingrestrictions'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('Add Shipping Restriction Rule'));

        return $resultPage;
    }
}
