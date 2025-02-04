<?php

namespace Murergrej\PalletShipping\Model\ResourceModel\Carrier\AbstractCarrier;

abstract class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Directory/country table name
     *
     * @var string
     */
    protected $countryTable;

    /**
     * Directory/country_region table name
     *
     * @var string
     */
    protected $regionTable;

    /**
     * Define resource model and item
     *
     * @return void
     */
    protected function _construct()
    {
        $this->countryTable = $this->getTable('directory_country');
        $this->regionTable = $this->getTable('directory_country_region');
    }

    /**
     * Initialize select, add country iso3 code and region name
     *
     * @return void
     */
    public function _initSelect()
    {
        parent::_initSelect();

        $this->_select->joinLeft(
            ['country_table' => $this->countryTable],
            'country_table.country_id = main_table.dest_country_id',
            ['dest_country' => 'iso3_code']
        )->joinLeft(
            ['region_table' => $this->regionTable],
            'region_table.region_id = main_table.dest_region_id',
            ['dest_region' => 'code']
        );

        $this->addOrder('dest_country', self::SORT_ORDER_ASC);
        $this->addOrder('dest_region', self::SORT_ORDER_ASC);
        $this->addOrder('dest_zip', self::SORT_ORDER_ASC);
    }

    /**
     * Add website filter to collection
     *
     * @param int $websiteId
     * @return \Murergrej\PalletShipping\Model\ResourceModel\Carrier\AbstractCarrier\Collection
     */
    public function setWebsiteFilter($websiteId)
    {
        return $this->addFieldToFilter('website_id', $websiteId);
    }

    /**
     * Add country filter to collection
     *
     * @param string $countryId
     * @return \Murergrej\PalletShipping\Model\ResourceModel\Carrier\AbstractCarrier\Collection
     */
    public function setCountryFilter($countryId)
    {
        return $this->addFieldToFilter('dest_country_id', $countryId);
    }
}
