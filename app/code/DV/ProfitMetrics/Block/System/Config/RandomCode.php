<?php
/**
 * Module DV_ProfitMetrics
 *
 * @category   DV
 * @package    DV_ProfitMetrics
 * @copyright  Copyright (c) 2020 DV
 */

namespace DV\ProfitMetrics\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class RandomCode
 * @package DV\ProfitMetrics\Block\System\Config
 */
class RandomCode extends Field
{
    /**
     * @var string
     */
    protected $_template = 'DV_ProfitMetrics::system/config/generate-random-code.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('profitmetrics/random/codegenerate');
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'generate_random_code_button',
                'label' => __('Generate Random Code'),
            ]
        );

        return $button->toHtml();
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
