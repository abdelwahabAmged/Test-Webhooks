<?php

namespace Murergrej\Sales\Plugin\AdminOrder;

use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;

class Create
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $metadataFormFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        \Magento\Customer\Model\Metadata\FormFactory $metadataFormFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->metadataFormFactory = $metadataFormFactory;
    }

    /**
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param \Magento\Quote\Model\Quote\Address|array $address
     * @return array|null
     */
    public function beforeSetBillingAddress(\Magento\Sales\Model\AdminOrder\Create $subject, $address)
    {
        return $this->beforeSaveAddress($subject, $address);
    }

    /**
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param \Magento\Quote\Model\Quote\Address|array $address
     * @return array|null
     */
    public function beforeSetShippingAddress(\Magento\Sales\Model\AdminOrder\Create $subject, $address)
    {
        return $this->beforeSaveAddress($subject, $address);
    }

    /**
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param \Magento\Quote\Model\Quote\Address|array $address
     * @return array|null
     */
    protected function beforeSaveAddress(\Magento\Sales\Model\AdminOrder\Create $subject, $address)
    {
        if (empty($address['save_in_address_book'])) {
            return null;
        }

        $customer = $subject->getQuote()->getCustomer();
        if (!$customer || !$customer->getId()) {
            return null;
        }

        $addressCollection = $this->collectionFactory->create();
        if (is_array($address)) {
            /** @var \Magento\Quote\Model\Quote\Address $addressModel */
            $addressModel = $addressCollection->getNewEmptyItem();
            $addressModel->setData($address);
        } else if ($address instanceof \Magento\Quote\Model\Quote\Address) {
            $addressModel = $address;
        } else {
            return null;
        }

        $addressForm = $this->metadataFormFactory->create('customer_address', 'adminhtml_customer_address');
        $attributes = $addressForm->getAttributes();

        foreach ($attributes as $attribute) {
            if ($attribute->getAttributeCode() == 'street') {
                $value = $addressModel->getStreetFull();
            } else {
                $value = $addressModel->getData($attribute->getAttributeCode());
            }
            if ($value == '') {
                $addressCollection->addFieldToFilter([
                    [
                        'attribute' => $attribute->getAttributeCode(),
                        'eq' => ''
                    ],
                    [
                        'attribute' => $attribute->getAttributeCode(),
                        'null' => true
                    ]
                ]);
            } else {
                $addressCollection->addAttributeToFilter($attribute->getAttributeCode(), $value);
            }
        }

        $addressCollection->addAttributeToFilter('parent_id', $customer->getId());

        $size = $addressCollection->getSize();
        if (!$size) {
            return null;
        }

        $address['save_in_address_book'] = 0;
        return [$address];
    }
}
