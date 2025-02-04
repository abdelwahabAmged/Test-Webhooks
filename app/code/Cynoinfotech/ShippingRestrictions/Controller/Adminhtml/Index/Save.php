<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Cynoinfotech\ShippingRestrictions\Model\ShippingrestrictionsFactory;
use Magento\Backend\Model\Session;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Cynoinfotech\ShippingRestrictions\Model\ShippingrestrictionsFactory
     */
    protected $shippingRestrictionsFactory;
    
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
        Action\Context $context,
        Session $session,
        ShippingrestrictionsFactory $shippingRestrictionsFactory
    ) {
        $this->session = $session;
        $this->shippingRestrictionsFactory = $shippingRestrictionsFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cynoinfotech_ShippingRestrictions::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $model = $this->shippingRestrictionsFactory->create();
            $data['carriers'] = implode(',', $data['carriers']);
            $data['customer_group'] = implode(',', $data['customer_group']);
            $data['stores'] = implode(',', $data['stores']);
            
            if (isset($data['days'])) {
                $data['days'] = implode(',', $data['days']);
            } else {
                $data['days'] ='';
            }
            
            $id = $this->getRequest()->getParam('sr_id');
            if ($id) {
                $model->load($id);
            }

            $data = $this->prepareData($data);
            $model->loadPost($data);
            $this->_session->setPageData($model->getData());
                      
            try {
                $model->save();
                $this->messageManager->addSuccess(__('Shipping Restriction Rules was successfully saved'));
                $this->session->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }

                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __(
                    $e->getMessage().'Something went wrong while saving the page.'
                ));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('sr_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
    
    /**
     * Prepares specific data
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }
        unset($data['rule']);
        return $data;
    }
}
