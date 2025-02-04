<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Checkout\Controller\Sidebar;

use Exception;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

/**
 * Controller for removing quote item from shopping cart.
 */
class RemoveItem extends Action implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     * @param ResultJsonFactory $resultJsonFactory
     * @param Sidebar $sidebar
     * @param Validator $formKeyValidator
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @param CompositeConfigProvider $checkoutConfig
     */
    public function __construct(
        Context $context,
        ResultRedirectFactory $resultRedirectFactory,
        protected RequestInterface $request,
        protected ResultJsonFactory $resultJsonFactory,
        protected Sidebar $sidebar,
        protected Validator $formKeyValidator,
        protected LoggerInterface $logger,
        protected Data $jsonHelper,
        protected CompositeConfigProvider $checkoutConfig,
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Execute method for removing item from sidebar cart and dispatching event.
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            return $this->resultRedirectFactory->create()
                ->setPath('*/cart/');
        }

        $itemId = (int)$this->request->getParam('item_id');
        $error = '';

        try {
            // Check if the item exists in the quote
            $this->sidebar->checkQuoteItem($itemId);
            // Remove the item from the quote
            $this->sidebar->removeQuoteItem($itemId);
            
            // Dispatch event after removing the item
            $this->_eventManager->dispatch(
                'checkout_cart_remove_item_after',
                ['quote_item_id' => $itemId, 'request' => $this->getRequest()]
            );

            return $this->jsonResponse([
                'message' => __('Item removed successfully.'),
                'checkoutConfig' => $this->checkoutConfig->getConfig()
            ]);
        } catch (LocalizedException $e) {
            $error = $e->getMessage();
        } catch (\Zend_Db_Exception $e) {
            $this->logger->critical($e);
            $error = __('An unspecified error occurred. Please contact us for assistance.');
        } catch (Exception $e) {
            $this->logger->critical($e);
            $error = $e->getMessage();
        }

        return $this->jsonResponse(['message' => $error], false);
    }

    /**
     * @param mixed $data
     * @param bool $success
     * @return Http
     */
    protected function jsonResponse(mixed $data = [], bool $success = true): Http
    {
        $response = [
            'success' => $success,
            'message' => isset($data['message']) ? $data['message'] : '',
        ];

        if (isset($data['checkoutConfig'])) {
            $response['checkoutConfig'] = $data['checkoutConfig'];
        }

        return $this->getResponse()
            ->representJson(
                $this->jsonHelper->jsonEncode($response)
            );
    }
}
