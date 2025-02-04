<?php

namespace Murergrej\PalletShipping\Model\ResourceModel\Carrier;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractCarrier extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const COLUMN_ID_COUNTRY = 0;
    const COLUMN_ID_REGION = 1;
    const COLUMN_ID_CITY = 2;
    const COLUMN_ID_ZIP = 3;
    const COLUMN_ID_ZIP_TO = 4;
    const COLUMN_ID_PALLET_QTY_FROM = 5;
    const COLUMN_ID_PALLET_QTY_TO = 6;
    const COLUMN_ID_WEIGHT_FROM = 7;
    const COLUMN_ID_WEIGHT_TO = 8;
    const COLUMN_ID_PRICE = 9;
    const COLUMN_ID_COST = 10;

    /**
     * Import table rates website ID
     *
     * @var int
     */
    protected $importWebsiteId = 0;

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $importErrors = [];

    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $importedRows = 0;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $importUniqueHash = [];

    /**
     * Array of countries keyed by iso2 code
     *
     * @var array
     */
    protected $importIso2Countries;

    /**
     * Array of countries keyed by iso3 code
     *
     * @var array
     */
    protected $importIso3Countries;

    /**
     * Associative array of countries and regions
     * [country_id][region_code] = region_id
     *
     * @var array
     */
    protected $importRegions;

    /**
     * Import Table Rate condition name
     *
     * @var string
     */
    protected $importConditionName;

    /**
     * Array of condition full names
     *
     * @var array
     */
    protected $conditionFullNames = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $coreConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \WebShopApps\MatrixRate\Model\Carrier\Matrixrate
     */
    protected $carrierMatrixrate;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $regionCollectionFactory;

    /**
     *   * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readFactory;
    /**
     *   * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \WebShopApps\MatrixRate\Model\Carrier\Matrixrate $carrierMatrixrate
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \WebShopApps\MatrixRate\Model\Carrier\Matrixrate $carrierMatrixrate,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Filesystem $filesystem,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->coreConfig = $coreConfig;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->carrierMatrixrate = $carrierMatrixrate;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->readFactory = $readFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Return table rate array or false by rate request
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param bool                                           $zipRangeSet
     *
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request, $zipRangeSet = false)
    {
        $adapter = $this->getConnection();
        $shippingData=[];
        $postcode = trim($request->getDestPostcode()); //SHQ18-1978
        if ($zipRangeSet && is_numeric($postcode)) {
            #  Want to search for postcodes within a range. SHQ18-98 Can't use bind. Will convert int to string
            $zipSearchString = ' AND ' .(int)$postcode. ' BETWEEN dest_zip AND dest_zip_to ';
        } else {
            $zipSearchString = " AND :postcode LIKE dest_zip ";
        }

        for ($j=0; $j<8; $j++) {
            $select = $adapter->select()->from(
                $this->getMainTable()
            )->where(
                'website_id = :website_id'
            )->order(
                ['dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC', 'pallet_qty_from DESC', 'weight_from DESC']
            );

            $zoneWhere='';
            $bind=[];
            switch ($j) {
                case 0: // country, region, city, postcode
                    $zoneWhere =  "dest_country_id = :country_id AND dest_region_id = :region_id AND STRCMP(LOWER(dest_city),LOWER(:city))= 0 " .$zipSearchString;
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':city' => $request->getDestCity(),
                        ':postcode' => $postcode,
                    ];
                    break;
                case 1: // country, region, no city, postcode
                    $zoneWhere =  "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city='*' "
                        .$zipSearchString;
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':postcode' => $postcode,
                    ];
                    break;
                case 2: // country, state, city, no postcode
                    $zoneWhere = "dest_country_id = :country_id AND dest_region_id = :region_id AND STRCMP(LOWER(dest_city),LOWER(:city))= 0 AND dest_zip ='*'";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':city' => $request->getDestCity(),
                    ];
                    break;
                case 3: //country, city, no region, no postcode
                    $zoneWhere =  "dest_country_id = :country_id AND dest_region_id = '0' AND STRCMP(LOWER(dest_city),LOWER(:city))= 0 AND dest_zip ='*'";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':city' => $request->getDestCity(),
                    ];
                    break;
                case 4: // country, postcode
                    $zoneWhere =  "dest_country_id = :country_id AND dest_region_id = '0' AND dest_city ='*' "
                        .$zipSearchString;
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':postcode' => $postcode,
                    ];
                    break;
                case 5: // country, region
                    $zoneWhere =  "dest_country_id = :country_id AND dest_region_id = :region_id  AND dest_city ='*' AND dest_zip ='*'";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                    ];
                    break;
                case 6: // country
                    $zoneWhere =  "dest_country_id = :country_id AND dest_region_id = '0' AND dest_city ='*' AND dest_zip ='*'";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                    ];
                    break;
                case 7: // nothing
                    $zoneWhere =  "dest_country_id = '0' AND dest_region_id = '0' AND dest_city ='*' AND dest_zip ='*'";
                    break;
            }

            $select->where($zoneWhere);

            $bind[':website_id'] = (int)$request->getWebsiteId();
            $bind[':pallet_qty'] = $request->getPalletQty();
            $bind[':weight'] = $request->getPackageWeight();

            $conditions = ['pallet_qty', 'weight'];
            foreach ($conditions as $condition) {
                $select->where($condition . '_from < :' . $condition);
                $select->where($condition . '_to >= :' . $condition);
            }

            $this->logger->debug('SQL Select: ', $select->getPart('where'));
            $this->logger->debug('Bindings: ', $bind);

            $results = $adapter->fetchAll($select, $bind);

            if (!empty($results)) {
                $this->logger->debug('SQL Results: ', $results);
                foreach ($results as $data) {
                    $shippingData[]=$data;
                }
                break;
            }
        }

        return $shippingData;
    }

    /**
     * Upload table rate file and import data from it
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return \WebShopApps\MatrixRate\Model\ResourceModel\Carrier\Matrixrate
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function uploadAndImport(\Magento\Framework\DataObject $object)
    {
        //M2-24
        $importFieldData = $object->getFieldsetDataValue('import');
        if (empty($importFieldData['tmp_name'])) {
            return $this;
        }

        $website = $this->storeManager->getWebsite($object->getScopeId());
        $csvFile = $importFieldData['tmp_name'];

        $this->importWebsiteId = (int)$website->getId();
        $this->importUniqueHash = [];
        $this->importErrors = [];
        $this->importedRows = 0;

        //M2-20
        $tmpDirectory = ini_get('upload_tmp_dir')? $this->readFactory->create(realpath(ini_get('upload_tmp_dir')))
            : $this->filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $path = $tmpDirectory->getRelativePath($csvFile);
        $stream = $tmpDirectory->openFile($path);

        // check and skip headers
        $headers = $stream->readCsv();
        if ($headers === false || count($headers) < 11) {
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct Matrix Rates File Format.'));
        }

        $adapter = $this->getConnection();
        $adapter->beginTransaction();

        try {
            $rowNumber = 1;
            $importData = [];

            $this->_loadDirectoryCountries();
            $this->_loadDirectoryRegions();

            // delete old data by website
            $condition = [
                'website_id = ?' => $this->importWebsiteId
            ];
            $adapter->delete($this->getMainTable(), $condition);

            while (false !== ($csvLine = $stream->readCsv())) {
                $rowNumber++;

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }

                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = [];
                }
            }
            $this->_saveImportData($importData);
            $stream->close();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $adapter->rollback();
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        } catch (\Exception $e) {
            $adapter->rollback();
            $stream->close();
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while importing matrix rates.')
            );
        }

        $adapter->commit();

        if ($this->importErrors) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->importErrors)
            );
            throw new \Magento\Framework\Exception\LocalizedException($error);
        }

        return $this;
    }

    /**
     * Load directory countries
     *
     * @return \WebShopApps\MatrixRate\Model\ResourceModel\Carrier\Matrixrate
     */
    protected function _loadDirectoryCountries()
    {
        if ($this->importIso2Countries !== null && $this->importIso3Countries !== null) {
            return $this;
        }

        $this->importIso2Countries = [];
        $this->importIso3Countries = [];

        /** @var $collection \Magento\Directory\Model\ResourceModel\Country\Collection */
        $collection = $this->countryCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $this->importIso2Countries[$row['iso2_code']] = $row['country_id'];
            $this->importIso3Countries[$row['iso3_code']] = $row['country_id'];
        }

        return $this;
    }

    /**
     * Load directory regions
     *
     * @return \WebShopApps\MatrixRate\Model\ResourceModel\Carrier\Matrixrate
     */
    protected function _loadDirectoryRegions()
    {
        if ($this->importRegions !== null) {
            return $this;
        }

        $this->importRegions = [];

        /** @var $collection \Magento\Directory\Model\ResourceModel\Region\Collection */
        $collection = $this->regionCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $this->importRegions[$row['country_id']][$row['code']] = (int)$row['region_id'];
        }

        return $this;
    }

    /**
     * Return import condition full name by condition name code
     *
     * @param string $conditionName
     * @return string
     */
    protected function getConditionFullName($conditionName)
    {
        if (!isset($this->conditionFullNames[$conditionName])) {
            $name = $this->carrierMatrixrate->getCode('condition_name_short', $conditionName);
            $this->conditionFullNames[$conditionName] = $name;
        }

        return $this->conditionFullNames[$conditionName];
    }

    /**
     * Validate row for import and return table rate array or false
     * Error will be add to importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        // validate row
        if (count($row) < 11) {
            $this->importErrors[] =
                __('Please correct Matrix Rates format in Row #%1. Invalid Number of Columns', $rowNumber);
            return false;
        }

        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        // validate country
        if (isset($this->importIso2Countries[$row[self::COLUMN_ID_COUNTRY]])) {
            $countryId = $this->importIso2Countries[$row[self::COLUMN_ID_COUNTRY]];
        } elseif (isset($this->importIso3Countries[$row[self::COLUMN_ID_COUNTRY]])) {
            $countryId = $this->importIso3Countries[$row[self::COLUMN_ID_COUNTRY]];
        } elseif ($row[self::COLUMN_ID_COUNTRY] == '*' || $row[self::COLUMN_ID_COUNTRY] == '') {
            $countryId = '0';
        } else {
            $this->importErrors[] = __('Please correct Country "%1" in Row #%2.', $row[self::COLUMN_ID_COUNTRY], $rowNumber);
            return false;
        }

        // validate region
        if ($countryId != '0' && isset($this->importRegions[$countryId][$row[self::COLUMN_ID_REGION]])) {
            $regionId = $this->importRegions[$countryId][$row[self::COLUMN_ID_REGION]];
        } elseif ($row[self::COLUMN_ID_REGION] == '*' || $row[self::COLUMN_ID_REGION] == '') {
            $regionId = 0;
        } else {
            $this->importErrors[] = __('Please correct Region/State "%1" in Row #%2.', $row[self::COLUMN_ID_REGION], $rowNumber);
            return false;
        }

        // detect city
        if ($row[self::COLUMN_ID_CITY] == '*' || $row[self::COLUMN_ID_CITY] == '') {
            $city = '*';
        } else {
            $city = $row[self::COLUMN_ID_CITY];
        }

        // detect zip code
        if ($row[self::COLUMN_ID_ZIP] == '*' || $row[self::COLUMN_ID_ZIP] == '') {
            $zipCode = '*';
        } else {
            $zipCode = $row[self::COLUMN_ID_ZIP];
        }

        //zip to
        if ($row[self::COLUMN_ID_ZIP_TO] == '*' || $row[self::COLUMN_ID_ZIP_TO] == '') {
            $zip_to = '';
        } else {
            $zip_to = $row[self::COLUMN_ID_ZIP_TO];
        }

        $palletQtyFrom = $this->_parseDecimalValue($row[self::COLUMN_ID_PALLET_QTY_FROM]);
        if ($palletQtyFrom === false) {
            $this->importErrors[] = __(
                'Please correct %1 From "%2" in Row #%3.',
                'Pallet Qty From',
                $row[self::COLUMN_ID_PALLET_QTY_FROM],
                $rowNumber
            );
            return false;
        }

        $palletQtyTo = $this->_parseDecimalValue($row[self::COLUMN_ID_PALLET_QTY_TO]);
        if ($palletQtyTo === false) {
            $this->importErrors[] = __(
                'Please correct %1 From "%2" in Row #%3.',
                'Pallet Qty To',
                $row[self::COLUMN_ID_PALLET_QTY_TO],
                $rowNumber
            );
            return false;
        }

        $weightFrom = $row[self::COLUMN_ID_WEIGHT_FROM] == '*' || $row[self::COLUMN_ID_WEIGHT_FROM] == -1 ? -1 : $this->_parseDecimalValue($row[self::COLUMN_ID_WEIGHT_FROM]);
        if ($weightFrom === false) {
            $this->importErrors[] = __(
                'Please correct %1 From "%2" in Row #%3.',
                'Weight From',
                $row[self::COLUMN_ID_WEIGHT_FROM],
                $rowNumber
            );
            return false;
        }

        $weightTo = $row[self::COLUMN_ID_WEIGHT_TO] == '*' ? 10000000 : $this->_parseDecimalValue($row[self::COLUMN_ID_WEIGHT_TO]);
        if ($weightTo === false) {
            $this->importErrors[] = __(
                'Please correct %1 To "%2" in Row #%3.',
                'Weight To',
                $row[self::COLUMN_ID_WEIGHT_TO],
                $rowNumber
            );
            return false;
        }

        // validate price
        $price = $this->_parseDecimalValue($row[self::COLUMN_ID_PRICE]);
        if ($price === false) {
            $this->importErrors[] = __('Please correct Shipping Price "%1" in Row #%2.', $row[self::COLUMN_ID_PRICE], $rowNumber);
            return false;
        }

        // validate cost
        $cost = $this->_parseDecimalValue($row[self::COLUMN_ID_COST]);
        if ($cost === false) {
            $this->importErrors[] = __('Please correct Shipping Cost "%1" in Row #%2.', $row[self::COLUMN_ID_COST], $rowNumber);
            return false;
        }

        // protect from duplicate
        $hash = sprintf(
            "%s-%s-%s-%s-%F-%F-%F-%F",
            $countryId,
            $city,
            $regionId,
            $zipCode,
            $palletQtyFrom,
            $palletQtyTo,
            $weightFrom,
            $weightTo
        );
        if (isset($this->importUniqueHash[$hash])) {
            $this->importErrors[] = __(
                'Duplicate Row #%1 (Country "%2", Region/State "%3", City "%4", Zip from "%5", Zip to "%6", Pallet Qty From "%7", Pallet Qty To "%8", Weight From "%9", Weight To "%10")',
                $rowNumber,
                $row[self::COLUMN_ID_COUNTRY],
                $row[self::COLUMN_ID_REGION],
                $city,
                $zipCode,
                $zip_to,
                $palletQtyFrom,
                $palletQtyTo,
                $weightFrom,
                $weightTo
            );
            return false;
        }
        $this->importUniqueHash[$hash] = true;

        return [
            $this->importWebsiteId,    // website_id
            $countryId,                 // dest_country_id
            $regionId,                  // dest_region_id,
            $city,                      // city,
            $zipCode,                   // dest_zip
            $zip_to,                    // zip to
            $palletQtyFrom,             // pallet qty From
            $palletQtyTo,               // pallet qty To
            $weightFrom,                // weight From
            $weightTo,                  // weight To
            $price,                     // price
            $cost                       // cost
        ];
    }

    /**
     * Save import data batch
     *
     * @param array $data
     *
     * @return \WebShopApps\MatrixRate\Model\ResourceModel\Carrier\Matrixrate
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = [
                'website_id',
                'dest_country_id',
                'dest_region_id',
                'dest_city',
                'dest_zip',
                'dest_zip_to',
                'pallet_qty_from',
                'pallet_qty_to',
                'weight_from',
                'weight_to',
                'price',
                'cost'
            ];
            $this->getConnection()->insertArray($this->getMainTable(), $columns, $data);
            $this->importedRows += count($data);
        }

        return $this;
    }

    /**
     * Parse and validate positive decimal value
     * Return false if value is not decimal or is not positive
     *
     * @param string $value
     * @return bool|float
     */
    protected function _parseDecimalValue($value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        $value = (double)sprintf('%.4F', $value);
        if ($value < 0.0000) {
            return false;
        }
        return $value;
    }
}
