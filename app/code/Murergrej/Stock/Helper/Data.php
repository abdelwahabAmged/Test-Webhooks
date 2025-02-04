<?php

namespace Murergrej\Stock\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Element\Template;
use Magento\InventoryApi\Api\StockRepositoryInterface;

class Data extends AbstractHelper
{
    const LOW_STOCK_SINGLE_ALERT_TEMPLATE_FILE =
        'Murergrej_Stock::notifications/low_stock_notification.phtml';

    const EMAIL_TEMPLATE = 'murergrej_stock_low_stock_notification_email_email_template';

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var mixed
     */
    protected $storeManager;

    /**
     * @var StockRepositoryInterface
     */
    protected $stockRepository;

    /**
     * @var Layout
     */
    protected $layout;

    /**
     * Data constructor.
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface|null $storeManager
     * @param StockRepositoryInterface $stockRepository
     * @param Layout $layout
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        StockRepositoryInterface $stockRepository,
        Layout $layout
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->stockRepository = $stockRepository;
        $this->layout = $layout;
        parent::__construct($context);
    }

    /**
     * @param $path
     * @param int $storeId
     * @return mixed
     */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(
            'murergrej_stock/' . $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabled() : bool
    {
        return (bool) $this->getModuleConfig('low_stock_notification/active');
    }

    /**
     * @return mixed
     */
    public function getQty()
    {
        return $this->getModuleConfig('low_stock_notification/qty');
    }

    /**
     * @return string
     */
    public function emailTemplate() : string
    {
        return (string) $this->getModuleConfig('low_stock_notification/email/email_template');
    }

    /**
     * @return string
     */
    public function emailSender() : string
    {
        return (string) $this->getModuleConfig('low_stock_notification/email/sender_email_identity');
    }

    /**
     * @return string
     */
    public function emailRecipient() : string
    {
        return (string) $this->getModuleConfig('low_stock_notification/email/recipient_email');
    }

    /**
     * @param array $lowStockItems
     * @return bool
     * @throws \Exception
     */
    public function notify($lowStockItems = [])
    {
        if (empty($lowStockItems)) {
            return false;
        }

        $this->inlineTranslation->suspend();
        try {
            $lowStockItemBlock = $this->layout->createBlock(Template::class)
                ->setTemplate(self::LOW_STOCK_SINGLE_ALERT_TEMPLATE_FILE)
                ->setData('lowStockItems', $lowStockItems);

            $lowStockHtml = $lowStockItemBlock->toHtml();

            $recipient = $this->emailRecipient();
            $recipient = explode(',', $recipient);
            $recipient = array_map('trim', $recipient);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->emailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars(['qty' => $this->getQty(),'lowStockHtml' => $lowStockHtml])
                ->setFrom($this->emailSender())
                ->addTo($recipient);

            $transport->getTransport()->sendMessage();
            return true;
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
