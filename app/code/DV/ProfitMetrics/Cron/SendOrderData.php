<?php


namespace DV\ProfitMetrics\Cron;

class SendOrderData
{
    /**
     * @var \DV\ProfitMetrics\Model\OrderSend
     */
    private $orderSendService;

    /**
     * SendOrderData constructor.
     * @param \DV\ProfitMetrics\Model\OrderSend $orderSendService
     */
    public function __construct(
        \DV\ProfitMetrics\Model\OrderSend $orderSendService
    ) {
        $this->orderSendService = $orderSendService;
    }

    public function execute(): void
    {
        $this->orderSendService->sendNewOrders();
    }
}
