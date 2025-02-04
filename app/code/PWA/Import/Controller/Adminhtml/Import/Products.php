<?php

namespace PWA\Import\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use PWA\Import\Model\Updater;

class Products extends Action implements HttpPostActionInterface
{
    /**
     * @var Updater
     */
    protected $updater;

    public function __construct(Action\Context $context, Updater $updater)
    {
        parent::__construct($context);
        $this->updater = $updater;
    }

    public function execute()
    {
        try {
            $this->updater->updateProducts();
            $this->messageManager->addSuccessMessage(__('Import was started.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('PWA_Import::import');
    }
}
