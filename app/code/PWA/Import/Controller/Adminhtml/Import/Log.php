<?php
namespace PWA\Import\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use PWA\Import\Model\HttpException;
use PWA\Import\Model\Updater;

class Log extends Action implements HttpGetActionInterface
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
            $response = $this->updater->getLog($this->getRequest()->getParam('offset'), $this->getRequest()->getParam('limit'));
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
