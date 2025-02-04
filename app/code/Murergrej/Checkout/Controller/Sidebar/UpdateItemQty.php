<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Checkout\Controller\Sidebar;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Checkout\Model\CompositeConfigProvider;

/**
 * Class used to update item quantity.
 */
class UpdateItemQty extends Action implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @param CompositeConfigProvider $checkoutConfig
     * @param RequestQuantityProcessor|null $quantityProcessor
     */
    public function __construct(
        Context $context,
        protected Sidebar $sidebar,
        protected LoggerInterface $logger,
        protected Data $jsonHelper,
        protected CompositeConfigProvider $checkoutConfig,
        protected ?RequestQuantityProcessor $quantityProcessor = null
    ) {
        parent::__construct($context);
        $this->quantityProcessor = $quantityProcessor
            ?? ObjectManager::getInstance()->get(RequestQuantityProcessor::class);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $itemId = (int)$this->getRequest()->getParam('item_id');
        $itemQty = (float)$this->getRequest()->getParam('item_qty') * 1;

        if ($itemQty <= 0) {
            return  $this->jsonResponse(__('Invalid Item Quantity Requested.'));
        }

        $itemQty = $this->quantityProcessor->prepareQuantity($itemQty);

        try {
            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->updateQuoteItem($itemId, $itemQty);

            return $this->jsonResponse([
                'message' => __('Item updated successfully.'),
                'checkoutConfig' => $this->checkoutConfig->getConfig()
            ]);
        } catch (LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
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
