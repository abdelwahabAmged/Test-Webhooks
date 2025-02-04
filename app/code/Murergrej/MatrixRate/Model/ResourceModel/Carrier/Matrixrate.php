<?php

namespace Murergrej\MatrixRate\Model\ResourceModel\Carrier;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Matrixrate extends \WebShopApps\MatrixRate\Model\ResourceModel\Carrier\Matrixrate
{
    /**
     *   * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \WebShopApps\MatrixRate\Model\Carrier\Matrixrate $carrierMatrixrate,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        Filesystem\Directory\ReadFactory $readFactory,
        Filesystem $filesystem,
        $resourcePrefix = null
    ) {
        $this->readFactory = $readFactory;
        parent::__construct($context, $logger, $coreConfig, $storeManager, $carrierMatrixrate, $countryCollectionFactory, $regionCollectionFactory, $readFactory, $filesystem, $resourcePrefix);
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
        $postcode = $request->getDestPostcode() ? trim($request->getDestPostcode()) : ''; //SHQ18-1978
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
            )->where(
                'order_total <= :order_total'
            )->order(
                ['order_total DESC', 'dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC', 'condition_from_value DESC']
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
            $bind[':condition_name'] = $request->getConditionMRName();
            $bind[':order_total'] = (float)$request->getPackageValue();

            //SHQ18-1978
            $condition = $request->getData($request->getConditionMRName());

            if ($condition == null || $condition == "") {
                $condition = 0;
            }

            $bind[':condition_value'] = $condition;

            $select->where('condition_name = :condition_name');
            $select->where('condition_from_value < :condition_value');
            $select->where('condition_to_value >= :condition_value');

            $this->logger->debug('SQL Select: ', $select->getPart('where'));
            $this->logger->debug('Bindings: ', $bind);

            $results = $adapter->fetchAll($select, $bind);

            if (!empty($results)) {
                $this->logger->debug('SQL Results: ', $results);
                foreach ($this->_filterResultsByOrderTotal($results) as $data) {
                    $shippingData[]=$data;
                }
                break;
            }
        }

        usort($shippingData, function ($a, $b) {
            if ($a['price'] == $b['price']) {
                return 0;
            }
            return $a['price'] < $b['price'] ? -1 : 1;
        });
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
        // MRP-282
        if (is_array($headers) && count($headers) == 1) {
            $headers = str_getcsv($headers[0]);
        }
        if ($headers === false || count($headers) < 5) {
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct Matrix Rates File Format.'));
        }

        if ($object->getData('groups/matrixrate/fields/condition_name/inherit') == '1') {
            $conditionName = (string)$this->coreConfig->getValue('carriers/matrixrate/condition_name', 'default');
        } else {
            $conditionName = $object->getData('groups/matrixrate/fields/condition_name/value');
        }
        $this->importConditionName = $conditionName;

        $adapter = $this->getConnection();
        $adapter->beginTransaction();

        try {
            $rowNumber = 1;
            $importData = [];

            $this->_loadDirectoryCountries();
            $this->_loadDirectoryRegions();

            // delete old data by website and condition name
            $condition = [
                'website_id = ?' => $this->importWebsiteId,
                'condition_name = ?' => $this->importConditionName,
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

    protected function _filterResultsByOrderTotal($results)
    {
        $orderTotals = [];
        foreach ($results as $row) {
            $orderTotal = (float)$row['order_total'];
            if (!$orderTotal) {
                continue;
            }

            $method = $row['shipping_method'];
            if (!isset($orderTotals[$method]) || $orderTotal > $orderTotals[$method]) {
                $orderTotals[$method] = $orderTotal;
            }
        }

        $result = array_filter($results, function ($value, $key) use ($orderTotals) {
            return !isset($orderTotals[$value['shipping_method']]) || (float)$orderTotals[$value['shipping_method']] <= (float)$value['order_total'];
        }, ARRAY_FILTER_USE_BOTH);

        return $result;
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
        // MRP-282
        if (count($row) == 1) {
            $row = str_getcsv($row[0]);
        }
        // validate row
        if (count($row) < 7) {
            $this->importErrors[] =
                __('Please correct Matrix Rates format in Row #%1. Invalid Number of Rows', $rowNumber);
            return false;
        }

        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        // validate country
        if (isset($this->importIso2Countries[$row[0]])) {
            $countryId = $this->importIso2Countries[$row[0]];
        } elseif (isset($this->importIso3Countries[$row[0]])) {
            $countryId = $this->importIso3Countries[$row[0]];
        } elseif ($row[0] == '*' || $row[0] == '') {
            $countryId = '0';
        } else {
            $this->importErrors[] = __('Please correct Country "%1" in Row #%2.', $row[0], $rowNumber);
            return false;
        }

        // validate region
        if ($countryId != '0' && isset($this->importRegions[$countryId][$row[1]])) {
            $regionId = $this->importRegions[$countryId][$row[1]];
        } elseif ($row[1] == '*' || $row[1] == '') {
            $regionId = 0;
        } else {
            $this->importErrors[] = __('Please correct Region/State "%1" in Row #%2.', $row[1], $rowNumber);
            return false;
        }

        // detect city
        if ($row[2] == '*' || $row[2] == '') {
            $city = '*';
        } else {
            $city = $row[2];
        }

        // detect zip code
        if ($row[3] == '*' || $row[3] == '') {
            $zipCode = '*';
        } else {
            $zipCode = $row[3];
        }

        //zip to
        if ($row[4] == '*' || $row[4] == '') {
            $zip_to = '';
        } else {
            $zip_to = $row[4];
        }

        // validate condition from value
        // MNB-472 Thanks to https://github.com/JeroenVanLeusden for the enhancement to accept -1
        $valueFrom = $row[5] == '*' || $row[5] == -1 ? -1 : $this->_parseDecimalValue($row[5]);
        if ($valueFrom === false) {
            $this->importErrors[] = __(
                'Please correct %1 From "%2" in Row #%3.',
                $this->getConditionFullName($this->importConditionName),
                $row[5],
                $rowNumber
            );
            return false;
        }
        // validate conditionto to value
        $valueTo = $row[6] == '*' ? 10000000 :$this->_parseDecimalValue($row[6]);
        if ($valueTo === false) {
            $this->importErrors[] = __(
                'Please correct %1 To "%2" in Row #%3.',
                $this->getConditionFullName($this->importConditionName),
                $row[6],
                $rowNumber
            );
            return false;
        }

        // validate price
        $price = $this->_parseDecimalValue($row[7]);
        if ($price === false) {
            $this->importErrors[] = __('Please correct Shipping Price "%1" in Row #%2.', $row[7], $rowNumber);
            return false;
        }

        // validate shipping method
        if ($row[8] == '*' || $row[8] == '') {
            $this->importErrors[] = __('Please correct Shipping Method "%1" in Row #%2.', $row[8], $rowNumber);
            return false;
        } else {
            $shippingMethod = $row[8];
        }

        // order total
        if (isset($row[9]) && $row[9] != '') {
            $orderTotal = $this->_parseDecimalValue($row[9]);
        } else {
            $orderTotal = 0;
        }

        // protect from duplicate
        $hash = sprintf(
            "%s-%s-%s-%s-%F-%F-%s-%s",
            $countryId,
            $city,
            $regionId,
            $zipCode,
            $valueFrom,
            $valueTo,
            $shippingMethod,
            $orderTotal
        );
        if (isset($this->importUniqueHash[$hash])) {
            $this->importErrors[] = __(
                'Duplicate Row #%1 (Country "%2", Region/State "%3", City "%4", Zip from "%5", Zip to "%6", From Value "%7", To Value "%8", Shipping Method "%9", Order Total "%10")',
                $rowNumber,
                $row[0],
                $row[1],
                $city,
                $zipCode,
                $zip_to,
                $valueFrom,
                $valueTo,
                $shippingMethod,
                $orderTotal
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
            $zip_to,                    //zip to
            $this->importConditionName,// condition_name,
            $valueFrom,                 // condition_value From
            $valueTo,                   // condition_value To
            $price,                     // price
            $shippingMethod,
            $orderTotal
        ];
    }

    /**
     * Save import data batch
     *
     * @param array $data
     *
     * @return \WebShopApps\MatrixRate\Model\ResourceModel\Carrier\MatrixRate
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
                'condition_name',
                'condition_from_value',
                'condition_to_value',
                'price',
                'shipping_method',
                'order_total'
            ];
            $this->getConnection()->insertArray($this->getMainTable(), $columns, $data);
            $this->importedRows += count($data);
        }

        return $this;
    }
}
