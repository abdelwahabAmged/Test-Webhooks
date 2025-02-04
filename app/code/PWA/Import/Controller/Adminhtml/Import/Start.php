<?php
namespace PWA\Import\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use PWA\Import\Model\HttpException;
use PWA\Import\Model\Updater;

class Start extends Action implements HttpPostActionInterface
{
    const MENU_ID = 'PWA_Import::import';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Updater
     */
    protected $updater;

    /**
     * Index constructor.
     *
     * @param Context $context

     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Updater $updater
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->updater = $updater;
    }

    /**
     * Load the page defined in view/adminhtml/layout/exampleadminnewpage_helloworld_index.xml
     *
     * @return Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            $response = $this->updater->startImport($this->getRequest()->getPost('command'));
            $result->setData($response);
        } catch (HttpException $e) {
            $result->setData($e->getResponse() ?? [
                    'error' => true,
                    'message' => $e->getMessage()
                ]);
        } catch (\Exception $e) {
            $result->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
        return $result;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('PWA_Import::import');
    }
}
