<?php

declare(strict_types=1);

namespace Murergrej\BrandWidget\Block\Adminhtml\Widget;

use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\App\ObjectManager;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Ui\Component\Form\Element\DataType\Media\OpenDialogUrl;
use Magento\Framework\Exception\LocalizedException;

class ImgUploader extends Template
{
    /**
     * @var BackendUrlInterface
     */
    private $backendUrl;

    /**
     * @var Factory
     */
    protected Factory $elementFactory;

    /**
     * @var OpenDialogUrl
     */
    private $openDialogUrl;

    /**
     * @var Images
     */
    private $imagesHelper;

    /**
     * @var string
     */
    private $currentTreePath;

    /**
     * @param Context $context
     * @param Factory $elementFactory
     * @param BackendUrlInterface $backendUrl
     * @param OpenDialogUrl $openDialogUrl
     * @param Images $imagesHelper
     * @param string $currentTreePath
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        BackendUrlInterface $backendUrl,
        OpenDialogUrl $openDialogUrl,
        Images $imagesHelper,
        $currentTreePath = 'wysiwyg',
        array $data = []
    ) {
        $this->backendUrl = $backendUrl;
        $this->elementFactory = $elementFactory;
        $this->imagesHelper = $imagesHelper;
        $this->currentTreePath = $currentTreePath;
        $this->openDialogUrl = $openDialogUrl;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     * @throws LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        $config = $this->_getData('config');
        $sourceUrl = $this->backendUrl->getUrl(
            $this->openDialogUrl->get(),
            [
                'current_tree_path' => $this->imagesHelper->idEncode($this->currentTreePath),
                'target_element_id' => $element->getId(),
                '_secure' => true
            ]
        );

        /** @var Button $chooser */
        $chooser = $this->getLayout()->createBlock(Button::class)
            ->setType('button')
            ->setClass('btn-chooser')
            ->setLabel($config['button']['open'])
            ->setOnClick('MediabrowserUtility.openDialog(\'' . $sourceUrl . '\')')
            ->setDisabled($element->getReadonly());

        /** @var Text $input */
        $input = $this->elementFactory->create("text", ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-text admin__control-text");
        $input->addCustomAttribute('data-force_static_path', 1);
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $element->setData('after_element_html', $input->getElementHtml() . $chooser->toHtml()
            . "<script>require(['mage/adminhtml/browser']);</script>");

        return $element;
    }
}
