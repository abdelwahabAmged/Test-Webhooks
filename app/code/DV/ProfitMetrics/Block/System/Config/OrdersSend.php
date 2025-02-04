<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

declare(strict_types=1);

namespace DV\ProfitMetrics\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Block\Widget\Button;

/**
 * Class GenerateFeed
 * @package DV\ProfitMetrics\Block\System\Config
 */
class OrdersSend extends Field
{
    /**
     * @var string
     */
    protected $_template = 'DV_ProfitMetrics::system/config/orders-send-now.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl('profitmetrics/orders/send');
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml(): string
    {
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id' => 'orders_send_button',
                'label' => __('Send Orders Now'),
            ]
        );

        return $button->toHtml();
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }
}
