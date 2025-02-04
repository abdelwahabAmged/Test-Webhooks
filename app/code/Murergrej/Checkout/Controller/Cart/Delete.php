<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @developer   Abanoub Youssef <info@scandiweb.com>
 *
 * This controller class handles the deletion of items from the shopping cart.
 */

declare(strict_types=1);

namespace Murergrej\Checkout\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Action Delete.
 *
 * Deletes item from cart.
 */
class Delete extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{
    /**
     * Delete shopping cart item action
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $id = (int)$this->getRequest()->getParam('id');

        if ($id) {
            try {
                $this->cart->removeItem($id);
                $this->_eventManager->dispatch(
                    'checkout_cart_remove_item_after',
                    ['quote' => $this->cart->getQuote()]
                );
                // We should set Totals to be recollected once more because of Cart model as usually is loading
                // before action executing and in case when triggerRecollect setted as true recollecting will
                // executed and the flag will be true already.
                $this->cart->getQuote()->setTotalsCollectedFlag(false);
                $this->cart->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('We can\'t remove the item.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
        }

        $defaultUrl = $this->_objectManager->create(\Magento\Framework\UrlInterface::class)->getUrl('*/*');

        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($defaultUrl));
    }
}
