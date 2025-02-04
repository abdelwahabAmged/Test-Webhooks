<?php

namespace Murergrej\PalletShipping\Model\Config\Backend;

abstract class AbstractCarrier extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Murergrej\PalletShipping\Model\ResourceModel\Carrier\AbstractCarrier
     */
    protected $carrierResource;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Murergrej\PalletShipping\Model\ResourceModel\Carrier\AbstractCarrier $carrierResource
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Murergrej\PalletShipping\Model\ResourceModel\Carrier\AbstractCarrier $carrierResource,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->carrierResource = $carrierResource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return \Magento\Framework\Model\AbstractModel|void
     */
    public function afterSave()
    {
        $this->carrierResource->uploadAndImport($this);
        return parent::afterSave();
    }
}
