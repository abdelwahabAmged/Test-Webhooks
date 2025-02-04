<?php

namespace Murergrej\PalletShipping\Block\Adminhtml\Carrier\Hcs;

class Grid extends \Murergrej\PalletShipping\Block\Adminhtml\Carrier\AbstractGrid
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Murergrej\PalletShipping\Model\ResourceModel\Carrier\HCS\CollectionFactory $collectionFactory,
        \Murergrej\PalletShipping\Model\Carrier\HCS $matrixrate,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $matrixrate, $data);
    }
}
