<?php
/**
 * Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Cynoinfotech\ShippingRestrictions\Model\ShippingrestrictionsFactory;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Cynoinfotech\ShippingRestrictions\Model\ShippingrestrictionsFactory
     */
     
    protected $shippingRestrictionsFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        ShippingrestrictionsFactory $shippingRestrictionsFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->shippingRestrictionsFactory = $shippingRestrictionsFactory;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cynoinfotech_ShippingRestrictions::save');
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('ShippingRestrictions'), __('ShippingRestrictions'))
            ->addBreadcrumb(__('Manage ShippingRestrictions'), __('Manage ShippingRestrictions'));
        return $resultPage;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->shippingRestrictionsFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                $model->load($id);
                $title = $model->getId();
                $model->delete();
                $this->messageManager->addSuccess(__('The Shipping Restriction Rule has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_shippingrestrictions_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a Shipping Restriction Rules to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
