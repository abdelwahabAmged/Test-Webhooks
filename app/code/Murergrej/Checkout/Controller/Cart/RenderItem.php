<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Checkout\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\Action\Context;

/**
 * Action RenderItem.
 *
 * Renders cart item html.
 */
class RenderItem extends Action
{
    /**
     * @param Context $context
     * @param ItemFactory $quoteItemFactory
     * @param QuoteFactory $quoteFactory
     * @param LayoutFactory $layoutFactory
     * @param RedirectInterface $redirect
     */
    public function __construct(
        Context $context,
        protected ItemFactory $quoteItemFactory,
        protected QuoteFactory $quoteFactory,
        protected LayoutFactory $layoutFactory,
        protected RedirectInterface $redirect
    ) {
        parent::__construct($context);
    }

    /**
     * Execute method to render item HTML
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('item_id');

        if ($itemId) {
            $quoteItem = $this->quoteItemFactory->create()->load($itemId);

            if ($quoteItem->getId()) {
                $response = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                $layout = $response->getLayout();
                $quote = $this->quoteFactory->create()->load($quoteItem->getQuoteId());

                $quoteItem->setQuote($quote);
                $response->addHandle('checkout_cart_index');

                $block = $layout->getBlock('checkout.cart.item.renderers.default');

                if ($block) {
                    $block->setItem($quoteItem);
                    $this->getResponse()->setBody($block->toHtml());
                }
            }
        }
    }
}
