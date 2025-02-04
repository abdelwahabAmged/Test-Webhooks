<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
         return true;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cynoinfotech_ShippingRestrictions::shippingrestrictions');
        $resultPage->addBreadcrumb(__('shippingrestrictions'), __('shippingrestrictions'));
        $resultPage->addBreadcrumb(__('Manage shippingrestrictions'), __('Manage shippingrestrictions'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Shipping Restrictions Rules'));
        return $resultPage;
    }
}
