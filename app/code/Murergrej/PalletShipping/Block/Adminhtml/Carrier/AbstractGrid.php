<?php

namespace Murergrej\PalletShipping\Block\Adminhtml\Carrier;

class AbstractGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Website filter
     *
     * @var int
     */
    protected $websiteId;

    /**
     * @var \Murergrej\PalletShipping\Model\Carrier\AbstractCarrier
     */
    protected $carrier;

    protected $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \WebShopApps\MatrixRate\Model\Carrier\Matrixrate $matrixrate
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Murergrej\PalletShipping\Model\Carrier\AbstractCarrier $carrier,
        array $data = []
    ) {
        $this->carrier = $carrier;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('shippingPalletGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->websiteId = $this->_storeManager->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if ($this->websiteId === null) {
            $this->websiteId = $this->_storeManager->getWebsite()->getId();
        }
        return $this->websiteId;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return \Murergrej\PalletShipping\Block\Adminhtml\Carrier\AbstractGrid
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Murergrej\PalletShipping\Model\ResourceModel\Carrier\AbstractCarrier\Collection */
        $collection = $this->collectionFactory->create();
        $collection->setWebsiteFilter($this->getWebsiteId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'dest_country',
            ['header' => __('Country'), 'index' => 'dest_country', 'default' => '*']
        );

        $this->addColumn(
            'dest_region',
            ['header' => __('Region/State'), 'index' => 'dest_region', 'default' => '*']
        );
        $this->addColumn(
            'dest_city',
            ['header' => __('City'), 'index' => 'dest_city', 'default' => '*']
        );
        $this->addColumn(
            'dest_zip',
            ['header' => __('Zip/Postal Code From'), 'index' => 'dest_zip', 'default' => '*']
        );
        $this->addColumn(
            'dest_zip_to',
            ['header' => __('Zip/Postal Code To'), 'index' => 'dest_zip_to', 'default' => '*']
        );

        $this->addColumn(
            'pallet_qty_from',
            ['header' => 'Pallet Qty '.__('>'), 'index' => 'pallet_qty_from']
        );

        $this->addColumn(
            'pallet_qty_to',
            ['header' => 'Pallet Qty '.__('<='), 'index' => 'pallet_qty_to']
        );

        $this->addColumn(
            'weight_from',
            ['header' => 'Weight '.__('>'), 'index' => 'weight_from']
        );

        $this->addColumn(
            'weight_to',
            ['header' => 'Weight '.__('<='), 'index' => 'weight_to']
        );

        $this->addColumn('price', ['header' => __('Shipping Price'), 'index' => 'price']);

        $this->addColumn('cost', ['header' => __('Shipping Cost'), 'index' => 'cost']);

        return parent::_prepareColumns();
    }
}
