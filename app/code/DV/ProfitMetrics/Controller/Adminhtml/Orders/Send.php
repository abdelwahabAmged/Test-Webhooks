<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Controller\Adminhtml\Orders;

class Send extends \Magento\Backend\App\Action
    implements \Magento\Framework\App\Action\HttpGetActionInterface,
    \Magento\Framework\App\Action\HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'DV_ProfitMetrics::orders_send';
    /**
     * @var \DV\ProfitMetrics\Model\OrderSend
     */
    private $orderSendService;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Send constructor.
     * @param \DV\ProfitMetrics\Model\OrderSend $orderSendService
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \DV\ProfitMetrics\Model\OrderSend $orderSendService,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->orderSendService = $orderSendService;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->orderSendService->sendNewOrders();

        return $this->resultJsonFactory->create()->setData(['result' => 'ok']);
    }
}
