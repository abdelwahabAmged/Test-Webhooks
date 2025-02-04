<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Cynoinfotech\ShippingRestrictions\Model\ResourceModel\Shippingrestrictions\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends \Magento\Backend\App\Action
{
    protected $_coreRegistry = null;

    protected $resultPageFactory;

    protected $ShipFactory;
   
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        Filter $filter,
        CollectionFactory $ShipFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->ShipFactory = $ShipFactory;
        $this->filter = $filter;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->ShipFactory->create());
        $count = 0;
        foreach ($collection as $shiprule) {
            $shiprule->delete();
            $count++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
