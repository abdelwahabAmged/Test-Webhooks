<?php

namespace Murergrej\InvoiceEmail\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action;
use \Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Save extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    protected $orderRepository;

    public function __construct(Action\Context $context, OrderRepositoryInterface $orderRepository)
    {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            $this->messageManager
                ->addErrorMessage(__('Order id was not specified.'));
            return $this->_redirect('sales/order/index');
        }

        try {
            $invoiceEmail = $this->getRequest()->getParam('invoice_email');
            $order = $this->orderRepository->get($orderId);
            $order->setData('invoice_email', $invoiceEmail);
            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }
}
