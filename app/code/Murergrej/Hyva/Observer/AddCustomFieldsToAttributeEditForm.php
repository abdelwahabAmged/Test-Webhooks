<?php
/**
 * @category  Murergrej
 * @package   Murergrej_Hyva
 * @author    Jorgena Shinjatari info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 * @license   https://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Murergrej\Hyva\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Config\Model\Config\Source\Yesno;

class AddCustomFieldsToAttributeEditForm implements ObserverInterface
{
    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @param Yesno $yesNo
     */
    public function __construct(Yesno $yesNo)
    {
        $this->_yesNo = $yesNo;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Get the form from the observer
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElement('front_fieldset');

        $yesnoSource = $this->_yesNo->toOptionArray();

        $fieldset->addField(
            'used_in_product_info_tabs',
            'select',
            [
                'name' => 'used_in_product_info_tabs',
                'label' => __('Used in product information tabs'),
                'title' => __('Used in product information tabs'),
                'values' => $yesnoSource,
            ]
        );

        $fieldset->addField(
            'sort_order_of_product_information_tabs',
            'text',
            [
                'name' => 'sort_order_of_product_information_tabs',
                'label' => __('Sort order of the tab'),
                'title' => __('Sort order of the tab'),
                'note' => __('Enter a non-negative digit value.'),
                'class' => 'validate-number',
            ]
        );
    }
}
