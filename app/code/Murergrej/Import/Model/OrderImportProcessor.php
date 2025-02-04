<?php

namespace Murergrej\Import\Model;

use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrderImportProcessor
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Resource connection adapter factory.
     *
     * @var ConnectionFactoryInterface
     */
    protected $connectionFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var Mysql\ConnectionSettings
     */
    protected $connectionSettings;

    /**
     * @var Synchronizer\Config
     */
    protected $config;

    /**
     * @var OutputInterface|null
     */
    protected $output;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $sourceConnection;

    protected $foreignKeys = [];
    protected $dependenciesTables = [];
    protected $dependentTables = [];
    protected $customDependencies = []; // eg quote_id in sales_order
    protected $customRecursiveDependencies = []; // eg billing_address_id in sales_order
    protected $customReverseDependencies = []; // eg order_grid from order

    protected $processedEntities = [];
    protected $notFoundEntities = [];

    protected $skipEntities = [
        'store',
        'store_website',
//        'customer_entity',
//        'customer_address_entity',
        'quote_id_mask',
        'eav_attribute',
        'catalog_product_entity',
        'catalog_category_entity',
        'customer_group',
        'report_viewed_product_index'
    ];

    protected $ignoreDependants = [
        'customer_entity' => [
            'sales_order'
        ],
        'sales_order_tax' => [
            'sales_order_tax_item'
        ]
    ];

    /**
     * @var bool
     */
    protected $test = false;
    protected $testLastInsertId = 1000000;

    protected $delayedDependencies = [];

    /**
     * @var int
     */
    protected $startId = 0;

    /**
     * @var int
     */
    protected $limit = -1;

    /**
     * @var string|null
     */
    protected $condition = null;

    /**
     * @var bool
     */
    protected $ignoreDateHour = false;

    protected $incrementIdSuffixForExistingEntities = 'PWA';

    protected $incrementIdSuffix = false;

    protected $timezone = false;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource, \Magento\Framework\App\ResourceConnection\ConnectionFactory $connectionFactory)
    {
        $this->resource = $resource;
        $this->connectionFactory = $connectionFactory;
    }

    public function setConnectionSettings(Mysql\ConnectionSettings $connectionSettings)
    {
        $this->connectionSettings = $connectionSettings;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setTest($value)
    {
        $this->test = (bool)$value;

        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setStartId($id)
    {
        $this->startId = $id;

        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param string $condition
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setIgnoreDateHour($value)
    {
        $this->ignoreDateHour = (bool)$value;

        return $this;
    }

    /**
     * @param string $suffix
     * @return $this
     */
    public function setIncrementIdSuffixForExistingEntities($suffix)
    {
        $this->incrementIdSuffixForExistingEntities = $suffix;

        return $this;
    }

    /**
     * @param string $suffix
     * @return $this
     */
    public function setIncrementIdSuffix($suffix)
    {
        $this->incrementIdSuffix = $suffix;

        return $this;
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function testTime()
    {
        $this->sourceConnection = $this->connectionFactory->create($this->connectionSettings->toArray());
        if ($this->timezone) {
            $this->sourceConnection->query('SET session time_zone = \'' . $this->timezone . '\'');
        }
        $this->connection = $this->resource->getConnection();

        $tableName = $this->resource->getTableName('sales_order');
        $sourceSelect = $this->sourceConnection->select()
            ->from($tableName, ['entity_id', 'created_at']);
        $this->configureSelect($sourceSelect);
        $currentSelect = $this->connection->select()
            ->from($tableName, ['entity_id', 'created_at']);
        $this->configureSelect($currentSelect);

        $sourceOrders = $this->sourceConnection->fetchAll($sourceSelect);
        $currentOrders = $this->connection->fetchAll($currentSelect);

        $result = [];
        foreach ($sourceOrders as $order) {
            $result[$order['entity_id']] = ['date1' => $order['created_at']];
        }
        foreach ($currentOrders as $order) {
            if (isset($result[$order['entity_id']])) {
                $result[$order['entity_id']]['date2'] = $order['created_at'];
            } else {
                $result[$order['entity_id']] = ['date2' => $order['created_at']];
            }
        }

        foreach ($result as $orderId => $dates) {
            $this->writeln($orderId . ' ' . (isset($dates['date1']) ? $dates['date1'] : '____') . ' ' . (isset($dates['date2']) ? $dates['date2'] : '____'));
        }
    }

    protected $ignore = [];
    public function printRelatedTables($table, $ignore = [])
    {
        $this->connection = $this->resource->getConnection();
        $this->collectTablesDependencies();

        $tables = [$table];
        $this->ignore = $ignore;
        $tables = array_merge($tables, $this->getRelatedTables($table));

        $this->writeln(implode(' ', $tables));
    }

    protected function getRelatedTables($table)
    {
        $this->ignore[] = $table;
        $result = [];
        foreach (['dependentTables', 'dependenciesTables'] as $container) {
            if (isset($this->{$container}[$table])) {
                foreach ($this->{$container}[$table] as $relatedTable => $keys) {
                    if (!in_array($relatedTable, $this->ignore)) {
                        $result[] = $relatedTable;
                        $result = array_merge($result, $this->getRelatedTables($relatedTable));
                    }
                }
            }
        }

        return $result;
    }

    public function importOrders()
    {
        $this->sourceConnection = $this->connectionFactory->create($this->connectionSettings->toArray());
        if ($this->timezone) {
            $this->sourceConnection->query('SET session time_zone = \'' . $this->timezone . '\'');
        }
        $this->connection = $this->resource->getConnection();

        $this->collectTablesDependencies();
        $this->initCustomDependencies();

        $restoreOrderIds = $this->getRestoreOrderIds();
        $this->writeln(implode(PHP_EOL, $restoreOrderIds));

        if ($this->test) {
            $this->restoreEntities('sales_order', $restoreOrderIds);
            $this->processDelayedDependencies();
        } else {
            $this->connection->beginTransaction();
            try {
                $this->restoreEntities('sales_order', $restoreOrderIds);
                $this->processDelayedDependencies();
                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollBack();
                throw $e;
            }
        }

        $this->writeln('Done.');
    }

    protected function restoreEntities($table, $ids, $forceCreate = false, $processingEntities = [])
    {
        $searchColumns = $this->getSearchFields($table);
        $this->validateIds($table, $ids, $searchColumns);

        if (in_array($table, $this->skipEntities)) {
            $result = [];
            foreach ($ids as $id) {
                $cacheId = $this->getEntityCacheValue($id, $searchColumns);
                $result[$cacheId] = $id;
            }
            return $result;
        }

        $identityColumn = $this->getIdentity($table);
        $result = [];

        foreach ($ids as $key => $id) {
            $cacheId = $this->getEntityCacheValue($id, $searchColumns);

            if (isset($this->processedEntities[$table][$cacheId])) {
                $result[$cacheId] = $this->processedEntities[$table][$cacheId];
                unset($ids[$key]);
            } else {
                $result[$cacheId] = false;
            }
        }

        if (empty($ids)) {
            return $result;
        }

        $select = $this->sourceConnection->select()
            ->from($table);
        $currentSelect = $this->connection->select()
            ->from($table);

        if (count($searchColumns) == 1) {
            $select->where($searchColumns[0] . ' IN (?)', $ids);
            $currentSelect->where($searchColumns[0] . ' IN (?)', $ids);
        } else {
            $fieldsStr = implode(', ', $searchColumns);
            $inStr = $this->quoteArrayOfArrays($ids);
            $select->where("($fieldsStr) IN ($inStr)");
            $currentSelect->where("($fieldsStr) IN ($inStr)");
        }

        $rows = []; // for updating values in row and inserting in current db
        foreach ($this->sourceConnection->fetchAll($select) as $row) {
            $cacheId = $this->getEntityCacheValue($row, $searchColumns);
            $rows[$cacheId] = $row;
        }
        $backupRowsOriginal = $rows; // for search dependent entities after parents inserted in current db

        if (count($rows) < count($ids)) {
            $notFound = [];
            foreach($ids as $id) {
                $found = false;
                if (is_array($id)) {
                    $cacheId = $this->getEntityCacheValue($id, $searchColumns);
                } else {
                    $cacheId = $id;
                }
                foreach ($rows as $row) {
                    if ($this->getEntityCacheValue($row, $searchColumns) == $cacheId) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $notFound[] = $cacheId;
                    $this->notFoundEntities[$table][] = $cacheId;
                }
            }
            $this->writeln('Not found entities for ' . $table . ': ' . implode(', ', $notFound));
        }

        if (empty($rows)) {
            $this->writeln('Source search result is empty. Select was ' . (string)$select);
            return [];
        }

        $currentRows = []; // to compare with backup
        foreach ($this->connection->fetchAll($currentSelect) as $row) {
            $cacheId = $this->getEntityCacheValue($row, $searchColumns);
            $currentRows[$cacheId] = $row;
        }

        $fullSearch = count($searchColumns) == count(current($rows));

        $differentEntities = [];
        if (!$forceCreate) {
            foreach ($rows as $cacheId => $row) {
                $differentEntities[$cacheId] = !isset($currentRows[$cacheId]) || $this->isEntityDifferent($table, $row, $currentRows[$cacheId]);
            }
            if (in_array(false, $differentEntities)) {
                $this->writeln('Following ' . $table . ' rows detected as unchanged: ' . implode(', ', array_keys(array_filter($differentEntities, function ($item) {
                        return !$item;
                    }))));
            }
        }

        if (!isset($processingEntities[$table])) {
            $processingEntities[$table] = [];
        }
        foreach ($rows as $cacheId => &$row) {
            if (in_array($cacheId, $processingEntities[$table])) {
                throw new \Exception('Entity ' . $table . ' ' . $cacheId . ' has already been processing');
            }
            $processingEntities[$table][] = $cacheId;
        }

        // update referenced tables
        $dependenciesTables = $this->getDependenciesTables($table);
        $changedRows = [];
        if ($dependenciesTables) {
            foreach ($dependenciesTables as $dependencyTable => $foreignKeys) {
                foreach ($foreignKeys as $foreignKey) {
                    $this->assertForeignKeyReferencesPrimaryKey($foreignKey);
                    $oldIds = $this->getReferencedIds($rows, $foreignKey);
                    if (empty($oldIds)) {
                        continue;
                    }

                    if ($table == $foreignKey['REF_TABLE_NAME']) {
                        $processingEntities[$table] = array_filter($processingEntities[$table], function ($item) use ($oldIds) {
                            return !in_array($item, $oldIds);
                        });
                    }

                    $newIds = $this->restoreEntities($foreignKey['REF_TABLE_NAME'], $oldIds, false, $processingEntities);

                    if ($table == $foreignKey['REF_TABLE_NAME']) {
                        foreach ($newIds as $oldId => $newId) {
                            unset($rows[$oldId]);
                            unset($backupRowsOriginal[$oldId]);
                            unset($currentRows[$oldId]);
                            $result[$oldId] = $newId;
                        }
                    }

                    foreach ($rows as $cacheId => &$row) {
                        /** only single-field foreign keys */
                        if (!is_null($row[$foreignKey['COLUMN_NAME']]) && isset($newIds[$row[$foreignKey['COLUMN_NAME']]]) && $row[$foreignKey['COLUMN_NAME']] != $newIds[$row[$foreignKey['COLUMN_NAME']]]) {
                            $row[$foreignKey['COLUMN_NAME']] = $newIds[$row[$foreignKey['COLUMN_NAME']]];
                            if ($fullSearch) {
                                $differentEntities[$cacheId] = true;
                            } else {
                                $changedRows[] = $cacheId;
                            }
                        }
                    }
                }
            }
        }

        $uniqueKey = false;
        if (in_array($table, ['customer_entity_int', 'customer_entity_varchar', 'customer_entity_text', 'customer_entity_decimal', 'customer_entity_datetime'])) {
            $uniqueKey = ['attribute_id', 'entity_id'];
        } else if ($table == 'wishlist') {
            $uniqueKey = ['customer_id'];
        }

        // update current table
        foreach ($rows as $cacheId => &$row) {
            if (isset($this->processedEntities[$table][$cacheId])) {
                throw new \Exception('The entity ' . $table . ' ' . $cacheId . ' is already processed.');
                //continue;
            }

            $changedCurrent = false;
            if ($differentEntities[$cacheId] && $uniqueKey) {
                $select = $this->connection->select()
                    ->from($table);
                foreach ($uniqueKey as $field) {
                    $select->where($field . ' = ?', $row[$field]);
                }
                $existingRow = $this->connection->fetchRow($select);
                if ($existingRow) {
                    $differentEntities[$cacheId] = false;
                    $this->writeln('Found existing ' . $table . ' row ' . json_encode(array_filter($existingRow, function ($key) use ($searchColumns, $uniqueKey) {
                            return in_array($key, $searchColumns) || in_array($key, $uniqueKey);
                        }, ARRAY_FILTER_USE_KEY)) . ' for ' . $cacheId);
                    foreach ($searchColumns as $field) {
                        $row[$field] = $existingRow[$field];
                    }
                    $changedCurrent = true;
                }
            }

            if ($changedCurrent) {
                $update = array_filter($row, function($value, $field) use ($existingRow, $cacheId, $searchColumns, $uniqueKey) {
                    return !in_array($field, $searchColumns) && !in_array($field, $uniqueKey) && $existingRow[$field] != $value;
                }, ARRAY_FILTER_USE_BOTH);
                if (!empty($update)) {
                    $changedRows[] = $cacheId;
                }
            } else {
                $update = array_filter($row, function ($value, $key) use ($backupRowsOriginal, $cacheId) {
                    return $backupRowsOriginal[$cacheId][$key] != $value;
                }, ARRAY_FILTER_USE_BOTH);
            }

            if ($forceCreate || $differentEntities[$cacheId]) {
                if ($fullSearch && empty($update)) {
                    $this->writeln('Skip inserting ' . $table . ' ' . json_encode($row));
                } else {
                    if (in_array($table, ['sales_order', 'sales_invoice', 'sales_order_grid', 'sales_invoice_grid', 'sales_shipment', 'sales_creditmemo'])) {
                        if ($this->incrementIdSuffix) {
                            $row['increment_id'] .= $this->incrementIdSuffix;
                            $update['increment_id'] = $row['increment_id'];
                        }
                        $select = $this->connection->select()
                            ->from($table, ['entity_id'])
                            ->where('increment_id = ?', $row['increment_id']);
                        $existingId = $this->connection->fetchOne($select);
                        if ($existingId) {
                            if ($this->incrementIdSuffix) {
                                throw new \Exception('Entity ' . $table . ' with increment_id ' . $row['increment_id'] . ' already exists');
                            }
                            $newIncrementId = $row['increment_id'] . $this->incrementIdSuffixForExistingEntities;
                            $this->writeln('Changing increment id for entity_id ' . $existingId . ' from ' . $row['increment_id'] . ' to ' . $newIncrementId);
                            if (!$this->test) {
                                $this->connection->update($table, [
                                    'increment_id' => $newIncrementId
                                ], [
                                    'increment_id = ?' => $row['increment_id']
                                ]);
                            }
                        }
                    }

                    if ($identityColumn) {
                        $row[$identityColumn] = null;
                        $this->write('insert ' . $table . ' ' . $cacheId . ' => ');
                    } else {
                        $this->writeln('insert ' . $table . ' ' . $cacheId . ' => ' . $this->getEntityCacheValue($row, $searchColumns));
                    }

                    if (!$this->test) {
                        try {
                            $this->connection->insert($table, $row);
                        } catch (\Exception $e) {
                            throw $e;
                        }
                    }

                    if ($identityColumn) {
                        if ($this->test) {
                            $lastInsertId = ++$this->testLastInsertId;
                        } else {
                            $lastInsertId = $this->connection->lastInsertId($table);
                        }
                        $row[$identityColumn] = $lastInsertId;
                        $this->writeln($lastInsertId);
                    }
                    $this->writeln('Changed ' . json_encode($update));
                }
            } else if (in_array($cacheId, $changedRows)) {
                $searchValues = $this->getEntitySearchValues($row, $searchColumns);
                $this->updateEntity($table, $cacheId, $update, $searchValues);
            }

            $this->processedEntities[$table][$cacheId] = $result[$cacheId] = $this->getEntitySearchId($row, $searchColumns);
        }

        // update dependent tables
        $dependentTables = $this->getDependentTables($table);
        if ($dependentTables) {
            if ($forceCreate) {
                $rowGroups = [
                    [
                        'rows' => $backupRowsOriginal,
                        'create' => true
                    ]
                ];
            } else {
                $backupRowsInserted = array_filter($backupRowsOriginal, function ($cacheId) use ($differentEntities) {
                    return $differentEntities[$cacheId];
                }, ARRAY_FILTER_USE_KEY);
                $backupRowsNotChanged = array_filter($backupRowsOriginal, function ($cacheId) use ($differentEntities) {
                    return !$differentEntities[$cacheId];
                }, ARRAY_FILTER_USE_KEY);
                $rowGroups = [];
                if (!empty($backupRowsInserted)) {
                    $rowGroups[] = [
                        'rows' => $backupRowsInserted,
                        'create' => false // qwe
                    ];
                }
                if (!empty($backupRowsNotChanged)) {
                    $rowGroups[] = [
                        'rows' => $backupRowsNotChanged,
                        'create' => false
                    ];
                }
            }

            foreach ($dependentTables as $dependentTable => $foreignKeys) {
                foreach ($foreignKeys as $foreignKey) {
                    if (isset($this->ignoreDependants[$table]) && in_array($foreignKey['TABLE_NAME'], $this->ignoreDependants[$table])) {
                        continue;
                    }
                    foreach ($rowGroups as $rowGroup) {
                        $referencingIds = $this->getReferencingIds($rowGroup['rows'], $foreignKey);
                        if (empty($referencingIds)) {
                            continue;
                        }

                        $dependentEntitySearchFields = $this->getSearchFields($foreignKey['TABLE_NAME']);
                        $select = $this->sourceConnection->select()
                            ->from($foreignKey['TABLE_NAME'], $dependentEntitySearchFields)
                            ->where($foreignKey['COLUMN_NAME'] . ' IN (?)', $referencingIds);
                        if (count($dependentEntitySearchFields) == 1) {
                            $dependentEntityIds = $this->sourceConnection->fetchCol($select);
                        } else {
                            $dependentEntityIds = $this->sourceConnection->fetchAll($select);
                        }

                        foreach ($dependentEntityIds as $key => $dependentEntityId) {
                            $dependentEntityCacheId = $this->getEntityCacheValue($dependentEntityId, $dependentEntitySearchFields);
                            if (isset($processingEntities[$foreignKey['TABLE_NAME']]) && in_array($dependentEntityCacheId, $processingEntities[$foreignKey['TABLE_NAME']])) {
                                unset($dependentEntityIds[$key]);
                            }
                        }

                        if (!empty($dependentEntityIds)) {
                            $this->restoreEntities($foreignKey['TABLE_NAME'], $dependentEntityIds, $rowGroup['create'], $processingEntities);
                        }
                    }
                }
            }
        }

        $customDependencies = $this->getRecursiveDependencies($table);
        if ($customDependencies) {
            foreach ($customDependencies as $dependencyTable => $foreignKeys) {
                foreach ($foreignKeys as $foreignKey) {
                    $this->assertForeignKeyReferencesPrimaryKey($foreignKey);
                    foreach ($rows as $cacheId => &$row) {
                        $oldReference = $row[$foreignKey['COLUMN_NAME']];
                        if (empty($oldReference)) {
                            continue;
                        }

                        $searchValues = $this->getEntitySearchValues($row, $searchColumns);
                        if (isset($this->processedEntities[$foreignKey['REF_TABLE_NAME']][$oldReference])) {
                            $newReference = $this->processCustomDependency($table, $cacheId, $searchValues, $foreignKey, $oldReference);

                            $row[$foreignKey['COLUMN_NAME']] = $newReference;
                        } else {
                            $this->delayedDependencies[$table][] = [
                                'cacheId' => $cacheId,
                                'searchValues' => $searchValues,
                                'foreignKey' => $foreignKey,
                                'oldReference' => $oldReference
                            ];
                        }
                    }
                }
            }
        }

        return $result;
    }

    protected function processCustomDependency($table, $cacheId, $searchValues, $foreignKey, $oldReference)
    {
        if (!isset($this->processedEntities[$foreignKey['REF_TABLE_NAME']][$oldReference])) {
            if (isset($this->notFoundEntities[$table]) && in_array($cacheId, $this->notFoundEntities[$table])) {
                $this->writeln('Cannot process custom dependency becouse referenced entity was not found. Entity:'  . $table . ':' . $cacheId . ' ' . print_r(['foreign_key' => $foreignKey, 'old_reference' => $oldReference], true));
            } else {
                // if some entities has been deleted from source database. For example sales_order_address
                $this->writeln('Referenced entity was not processed. Entity: ' . $table . ':' . $cacheId . ' ' . print_r(['foreign_key' => $foreignKey, 'old_reference' => $oldReference], true));
            }
            return $oldReference;
        }

        $newReference = $this->processedEntities[$foreignKey['REF_TABLE_NAME']][$oldReference];
        if ($oldReference == $newReference) {
            return $newReference;
        }

        $update = [
            $foreignKey['COLUMN_NAME'] => $newReference
        ];
        $this->updateEntity($table, $cacheId, $update, $searchValues);

        return $newReference;
    }

    protected function updateEntity($table, $cacheId, $update, $searchValues)
    {
        $where = $this->convertKeyValuesToWhere($searchValues);
        if (!$where) {
            throw new \Exception('Where condition is empty (update table' . $table . ', row ' . $cacheId .  ')');
        }

        $this->writeln('update ' . $table . ' ' . $cacheId . ' values ' . json_encode($update) . ' where ' . json_encode($where));
        if (!$this->test) {
            $updatedCount = $this->connection->update($table, $update, $where);
            if ($updatedCount != 1) {
                throw new \Exception('Updated rows count is ' . $updatedCount);
            }
        }
    }

    protected function processDelayedDependencies()
    {
        foreach ($this->delayedDependencies as $table => $items) {
            foreach ($items as $data) {
                $this->processCustomDependency($table, $data['cacheId'], $data['searchValues'], $data['foreignKey'], $data['oldReference']);
            }
        }
    }

    protected function convertKeyValuesToWhere($array)
    {
        $where = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                throw new \Exception('Array where should not be used.');
                //$where[$key . ' IN (?)'] = $value;
            } else {
                $where[$key . ' = ?'] = $value;
            }
        }
        return $where;
    }

    protected function validateIds($table, $ids, $searchColumns)
    {
        $searchColumnsCount = count($searchColumns);
        foreach ($ids as $id) {
            if ($searchColumnsCount == 1) {
                if (!is_array($id)) {
                    continue;
                }
            } else {
                if (is_array($id) && count($id) == $searchColumnsCount) {
                    continue;
                }
            }
            throw new \Exception('Invalid id for table ' . $table . ': ' . print_r($id, true));
        }
    }

    protected function isEntityDifferent($table, $backupRow, $currentRow)
    {
        switch ($table) {
            case 'sales_order':
                foreach (['protect_code', 'increment_id'] as $field) {
                    if ($backupRow[$field] != $currentRow[$field]) {
                        return true;
                    }
                }
                return false;
            case 'quote_item_option':
                foreach (['product_id', 'code', 'value'] as $field) {
                    if ($backupRow[$field] != $currentRow[$field]) {
                        return true;
                    }
                }
                return false;
            case 'sales_invoice_item':
            case 'sales_shipment_item':
            case 'sales_creditmemo_item':
            case 'sales_order_tax_item':
            case 'sales_order_address':
            case 'sales_order_payment':
            case 'swissup_afm_order_address':
            case 'swissup_afm_quote_address':
            case 'amasty_gdprcookie_cookie_consents':
            case 'catalog_category_product':
            case 'salesrule_customer_group':
            case 'salesrule_website':
            case 'salesrule_product_attribute':
                foreach ($backupRow as $field => $value) {
                    if ($backupRow[$field] != $currentRow[$field]) {
                        return true;
                    }
                }
                return false;
            case 'sales_order_tax':
            case 'vault_payment_token_order_payment_link':
                return true;
            case 'customer_entity_datetime':
            case 'customer_entity_decimal':
            case 'customer_entity_int':
            case 'customer_entity_text':
            case 'customer_entity_varchar':
            case 'catalog_category_entity_datetime':
            case 'catalog_category_entity_decimal':
            case 'catalog_category_entity_int':
            case 'catalog_category_entity_text':
            case 'catalog_category_entity_varchar':
                foreach (['attribute_id', 'entity_id'] as $field) {
                    if ($backupRow[$field] != $currentRow[$field]) {
                        return true;
                    }
                }
                return false;
            case 'wishlist':
                return $backupRow['customer_id'] != $currentRow['customer_id'];
            case 'report_viewed_product_index':
                foreach (['visitor_id', 'customer_id', 'product_id'] as $field) {
                    if ($backupRow[$field] != $currentRow[$field]) {
                        return true;
                    }
                }
                return false;
            case 'salesrule_coupon_usage':
                foreach (['coupon_id', 'customer_id'] as $field) {
                    if ($backupRow[$field] != $currentRow[$field]) {
                        return true;
                    }
                }
                return false;
            case 'salesrule_coupon':
                foreach (['code', 'created_at'] as $field) {
                    if ($backupRow[$field] != $currentRow[$field]) {
                        return true;
                    }
                }
                return false;
            case 'salesrule':
                if ($backupRow['name'] != $currentRow['name']) {
                    return true;
                } else {
                    return false;
                }
            case 'salesrule_customer':
                foreach (['rule_customer_id', 'rule_id', 'customer_id'] as $field) {
                    if ($backupRow[$field] != $currentRow[$field]) {
                        return true;
                    }
                }
                return false;
            case 'customer_entity':
                return $backupRow['email'] != $currentRow['email'];
        }

        if (array_key_exists('created_at', $backupRow)) {
            if ($this->ignoreDateHour) {
                $result1 = preg_match('/(\d{4}-\d\d-\d\d) \d\d:(\d\d:\d\d)/', $backupRow['created_at'], $backupDateParts);
                if (!$result1) {
                    $this->writeln('Cannot parse date. Table '.  $table . ' Data: ' . print_r($backupRow, true));
                }
                $result2 = preg_match('/(\d{4}-\d\d-\d\d) \d\d:(\d\d:\d\d)/', $currentRow['created_at'], $currentDateParts);
                if (!$result2) {
                    $this->writeln('Cannot parse date. Table '.  $table . ' Data: ' . print_r($currentRow, true));
                }
                if ($backupDateParts[1] != $currentDateParts[1] || $backupDateParts[2] != $currentDateParts[2]) {
                    return true;
                }
            } else {
                if ($backupRow['created_at'] != $currentRow['created_at']) {
                    return true;
                }
            }
        } else {
            throw new \Exception('Unable to check difference of entities ' . $table);
        }

        return false;
    }

    protected function getReferencingIds(&$rows, &$foreignKey)
    {
        return array_filter(array_map(function ($row) use ($foreignKey) {
            return $row[$foreignKey['REF_COLUMN_NAME']];
        }, $rows), function ($item) {
            return !is_null($item);
        });
    }

    protected function getReferencedIds(&$rows, &$foreignKey)
    {
        return array_filter(array_map(function ($row) use ($foreignKey) {
            return $row[$foreignKey['COLUMN_NAME']];
        }, $rows), function ($item) {
            return !is_null($item);
        });
    }

    protected function assertForeignKeyReferencesPrimaryKey($foreignKey)
    {
        $primaryKey = $this->getPrimaryKey($foreignKey['REF_TABLE_NAME']);
        if (count($primaryKey) != 1 || $primaryKey[0] != $foreignKey['REF_COLUMN_NAME']) {
            throw new \Exception('Assert foreign key references primary key failed. Foreign key ' . $foreignKey['FK_NAME']);
        }
    }

    protected function getIdentity($table)
    {
        $describeTable = $this->connection->describeTable($table);

        foreach ($describeTable as $column) {
            if ($column['IDENTITY']) {
                return $column['COLUMN_NAME'];
            }
        }

        return false;
    }

    protected function getPrimaryKey($table)
    {
        $describeTable = $this->connection->describeTable($table);

        return array_values(array_map(function($item) {
            return $item['COLUMN_NAME'];
        }, array_filter($describeTable, function ($item) {
            return $item['PRIMARY_POSITION'] != null;
        })));
    }

    protected function getSearchFields($table)
    {
        $describeTable = $this->connection->describeTable($table);

        $primaryKey = array_values(array_map(function($item) {
            return $item['COLUMN_NAME'];
        }, array_filter($describeTable, function ($item) {
            return $item['PRIMARY_POSITION'] != null;
        })));

        if (empty($primaryKey)) {
            $searchColumns = array_values(array_map(function($item) {
                return $item['COLUMN_NAME'];
            }, $describeTable));
        } else {
            $searchColumns = $primaryKey;
        }

        return $searchColumns;
    }

    protected function getEntitySearchId(&$row, &$fields)
    {
        $result = $this->getEntitySearchValues($row, $fields);
        if (count($fields) == 1) {
            return current($result);
        }
        return $result;
    }

    protected function getEntitySearchValues(&$row, &$fields)
    {
        return array_filter($row, function ($key) use ($fields) {
            return in_array($key, $fields);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function getEntityCacheValue(&$row, &$fields)
    {
        if (!is_array($row)) {
            if (count($fields) != 1) {
                throw new \Exception('For entity with multiple key fields, row should be an array.');
            }
            return (string)$row;
        }
        return implode('|', $this->getEntitySearchValues($row, $fields));
    }

    protected function getCurrentOrders()
    {
        $select = $this->connection->select()
            ->from($this->resource->getTableName('sales_order'), ['entity_id', 'increment_id']);
        return $this->connection->fetchAll($select);
    }

    protected function getRestoreOrderIds()
    {
        $select = $this->sourceConnection->select()
            ->from($this->resource->getTableName('sales_order'), 'entity_id');
            //->where('(`entity_id`, `increment_id`) NOT IN (' . $this->quoteArrayOfArrays($this->getCurrentOrders()) . ')');
        $this->configureSelect($select);
        return $this->sourceConnection->fetchCol($select);
    }

    protected function configureSelect($select)
    {
        if ($this->condition) {
            $select->where($this->condition);
        } else if ($this->startId > 0) {
            $select->where('entity_id >= ?', $this->startId);
        }
        if ($this->limit > 0) {
            $select->limit($this->limit);
        }
    }

    protected function quoteArrayOfArrays($array)
    {
        return  implode(', ', array_map(function ($item) {
            return '(' . $this->connection->quote($item) . ')';
        }, $array));
        /*$array = array_map(function ($item) {
            $item = array_map(function($value) {
                return $this->connection->quote($value);
            }, $item);
            return '(' . implode(', ', $item) . ')';
        });
        return implode(', ', $array);*/
    }

    protected function getDependenciesTables($table)
    {
        if (isset($this->dependenciesTables[$table])) {
            $result = $this->dependenciesTables[$table];
        } else {
            $result = [];
        }
        if (isset($this->customDependencies[$table])) {
            foreach ($this->customDependencies[$table] as $t => $foreignKeys) {
                if (isset($result[$t])) {
                    $result[$t] = array_merge($result[$t], $foreignKeys);
                } else {
                    $result[$t] = $foreignKeys;
                }
            }
        }
        return $result;
    }

    protected function getDependentTables($table)
    {
        if (isset($this->dependentTables[$table])) {
            $result = $this->dependentTables[$table];
        } else {
            $result = [];
        }
        if (isset($this->customReverseDependencies[$table])) {
            foreach ($this->customReverseDependencies[$table] as $t => $foreignKeys) {
                if (isset($result[$t])) {
                    $result[$t] = array_merge($result[$t], $foreignKeys);
                } else {
                    $result[$t] = $foreignKeys;
                }
            }
        }
        return $result;
    }

    protected function getRecursiveDependencies($table)
    {
        if (isset($this->customRecursiveDependencies[$table])) {
            return $this->customRecursiveDependencies[$table];
        }
        return [];
    }

    protected function collectTablesDependencies()
    {
        $tables = $this->connection->getTables();
        foreach ($tables as $table) {
            $foreignKeys = $this->connection->getForeignKeys($table);

            if (empty($foreignKeys)) {
                $createTable = $this->connection->getCreateTable($table);
                if (strpos($createTable, 'FOREIGN KEY') !== false) {
                    throw new \Exception('Not supported foreign key in table ' . $table);
                }
            }

            $this->foreignKeys[$table] = $foreignKeys;

            foreach ($foreignKeys as $foreignKey) {
                $this->dependenciesTables[$table][$foreignKey['REF_TABLE_NAME']][] = $foreignKey;
                $this->dependentTables[$foreignKey['REF_TABLE_NAME']][$table][] = $foreignKey;
            }
        }
    }

    protected function initCustomDependencies()
    {
        $this->customDependencies = [
            'sales_order' => [
                'quote' => [
                    [
                        'FK_NAME' => 'CUSTOM_ORDER_QUOTE',
                        'TABLE_NAME' => 'sales_order',
                        'COLUMN_NAME' => 'quote_id',
                        'REF_TABLE_NAME' => 'quote',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ],
                'quote_address' => [
                    [
                        'FK_NAME' => 'CUSTOM_ORDER_QUOTE_ADDRESS',
                        'TABLE_NAME' => 'sales_order',
                        'COLUMN_NAME' => 'quote_address_id',
                        'REF_TABLE_NAME' => 'quote_address',
                        'REF_COLUMN_NAME' => 'address_id'
                    ]
                ],
            ],
            // TODO: test order item parent_item_id
            'sales_order_item' => [
                'sales_order_item' => [
                    [
                        'FK_NAME' => 'CUSTOM_ORDER_ITEM_PARENT_ITEM',
                        'TABLE_NAME' => 'sales_order_item',
                        'COLUMN_NAME' => 'parent_item_id',
                        'REF_TABLE_NAME' => 'sales_order_item',
                        'REF_COLUMN_NAME' => 'item_id'
                    ]
                ]
            ],
        ];
        $this->customRecursiveDependencies = [
            'sales_order' => [
                'sales_order_address' => [
                    [
                        'FK_NAME' => 'CUSTOM_ORDER_BILLING_ADDRESS',
                        'TABLE_NAME' => 'sales_order',
                        'COLUMN_NAME' => 'billing_address_id',
                        'REF_TABLE_NAME' => 'sales_order_address',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ],
                    [
                        'FK_NAME' => 'CUSTOM_ORDER_SHIPPING_ADDRESS',
                        'TABLE_NAME' => 'sales_order',
                        'COLUMN_NAME' => 'shipping_address_id',
                        'REF_TABLE_NAME' => 'sales_order_address',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ]
            ],
            'sales_invoice' => [
                'sales_order_address' => [
                    [
                        'FK_NAME' => 'CUSTOM_INVOICE_BILLING_ADDRESS',
                        'TABLE_NAME' => 'sales_invoice',
                        'COLUMN_NAME' => 'billing_address_id',
                        'REF_TABLE_NAME' => 'sales_order_address',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ],
                    [
                        'FK_NAME' => 'CUSTOM_INVOICE_SHIPPING_ADDRESS',
                        'TABLE_NAME' => 'sales_invoice',
                        'COLUMN_NAME' => 'shipping_address_id',
                        'REF_TABLE_NAME' => 'sales_order_address',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ]
            ],
            'sales_shipment' => [
                'sales_order_address' => [
                    [
                        'FK_NAME' => 'CUSTOM_SHIPMENT_BILLING_ADDRESS',
                        'TABLE_NAME' => 'sales_shipment',
                        'COLUMN_NAME' => 'billing_address_id',
                        'REF_TABLE_NAME' => 'sales_order_address',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ],
                    [
                        'FK_NAME' => 'CUSTOM_SHIPMENT_SHIPPING_ADDRESS',
                        'TABLE_NAME' => 'sales_shipment',
                        'COLUMN_NAME' => 'shipping_address_id',
                        'REF_TABLE_NAME' => 'sales_order_address',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ]
            ],
            'sales_creditmemo' => [
                'sales_order_address' => [
                    [
                        'FK_NAME' => 'CUSTOM_CREDITMEMO_BILLING_ADDRESS',
                        'TABLE_NAME' => 'sales_creditmemo',
                        'COLUMN_NAME' => 'billing_address_id',
                        'REF_TABLE_NAME' => 'sales_order_address',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ],
                    [
                        'FK_NAME' => 'CUSTOM_CREDITMEMO_SHIPPING_ADDRESS',
                        'TABLE_NAME' => 'sales_creditmemo',
                        'COLUMN_NAME' => 'shipping_address_id',
                        'REF_TABLE_NAME' => 'sales_order_address',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ],
                'sales_invoice' => [
                    [
                        'FK_NAME' => 'CUSTOM_CREDITMEMO_INVOICE',
                        'TABLE_NAME' => 'sales_creditmemo',
                        'COLUMN_NAME' => 'invoice_id',
                        'REF_TABLE_NAME' => 'sales_invoice',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ]
            ],
            'sales_order_address' => [
                'quote_address' => [
                    [
                        'FK_NAME' => 'CUSTOM_ORDER_ADDRESS_QUOTE_ADDRESS',
                        'TABLE_NAME' => 'sales_order_address',
                        'COLUMN_NAME' => 'quote_address_id',
                        'REF_TABLE_NAME' => 'quote_address',
                        'REF_COLUMN_NAME' => 'address_id'
                    ]
                ],
                'customer_address_entity' => [
                    [
                        'FK_NAME' => 'CUSTOM_ORDER_ADDRESS_CUSTOMER_ADDRESS',
                        'TABLE_NAME' => 'sales_order_address',
                        'COLUMN_NAME' => 'customer_address_id',
                        'REF_TABLE_NAME' => 'customer_address_entity',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ]
            ],
            'sales_invoice_item' => [
                'sales_order_item' => [
                    [
                        'FK_NAME' => 'CUSTOM_INVOICE_ITEM_ORDER_ITEM',
                        'TABLE_NAME' => 'sales_invoice_item',
                        'COLUMN_NAME' => 'order_item_id',
                        'REF_TABLE_NAME' => 'sales_order_item',
                        'REF_COLUMN_NAME' => 'item_id'
                    ]
                ]
            ],
            'sales_shipment_item' => [
                'sales_order_item' => [
                    [
                        'FK_NAME' => 'CUSTOM_SHIPMENT_ITEM_ORDER_ITEM',
                        'TABLE_NAME' => 'sales_shipment_item',
                        'COLUMN_NAME' => 'order_item_id',
                        'REF_TABLE_NAME' => 'sales_order_item',
                        'REF_COLUMN_NAME' => 'item_id'
                    ]
                ]
            ],
            'customer_entity' => [
                'customer_address_entity' => [
                    [
                        'FK_NAME' => 'CUSTOM_CUSTOMER_ENTITY_DEFAULT_BILLING',
                        'TABLE_NAME' => 'customer_entity',
                        'COLUMN_NAME' => 'default_billing',
                        'REF_TABLE_NAME' => 'customer_address_entity',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ],
                    [
                        'FK_NAME' => 'CUSTOM_CUSTOMER_ENTITY_DEFAULT_SHIPPING',
                        'TABLE_NAME' => 'customer_entity',
                        'COLUMN_NAME' => 'default_shipping',
                        'REF_TABLE_NAME' => 'customer_address_entity',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ]
            ]
        ];
        $this->customReverseDependencies = [
            'sales_order' => [
                'sales_order_grid' => [
                    [
                        'FK_NAME' => 'CUSTOM_ORDER_GRID_ORDER',
                        'TABLE_NAME' => 'sales_order_grid',
                        'COLUMN_NAME' => 'entity_id',
                        'REF_TABLE_NAME' => 'sales_order',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ]
            ],
            'sales_invoice' => [
                'sales_invoice_grid' => [
                    [
                        'FK_NAME' => 'CUSTOM_INVOICE_GRID_INVOICE',
                        'TABLE_NAME' => 'sales_invoice_grid',
                        'COLUMN_NAME' => 'entity_id',
                        'REF_TABLE_NAME' => 'sales_invoice',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ]
            ],
            'sales_shipment' => [
                'sales_shipment_grid' => [
                    [
                        'FK_NAME' => 'CUSTOM_SHIPMENT_GRID_SHIPMENT',
                        'TABLE_NAME' => 'sales_shipment_grid',
                        'COLUMN_NAME' => 'entity_id',
                        'REF_TABLE_NAME' => 'sales_shipment',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ]
            ],
            'sales_creditmemo' => [
                'sales_creditmemo_grid' => [
                    [
                        'FK_NAME' => 'CUSTOM_CREDITMEMO_GRID_CREDITMEMO',
                        'TABLE_NAME' => 'sales_creditmemo_grid',
                        'COLUMN_NAME' => 'entity_id',
                        'REF_TABLE_NAME' => 'sales_creditmemo',
                        'REF_COLUMN_NAME' => 'entity_id'
                    ]
                ]
            ]
        ];

        $foreignKeys = [
            [
                'FK_NAME' => 'CUSTOM_ORDER_GRID_ORDER',
                'TABLE_NAME' => 'sales_order_grid',
                'COLUMN_NAME' => 'entity_id',
                'REF_TABLE_NAME' => 'sales_order',
                'REF_COLUMN_NAME' => 'entity_id'
            ],
            [
                'FK_NAME' => 'CUSTOM_INVOICE_GRID_INVOICE',
                'TABLE_NAME' => 'sales_invoice_grid',
                'COLUMN_NAME' => 'entity_id',
                'REF_TABLE_NAME' => 'sales_invoice',
                'REF_COLUMN_NAME' => 'entity_id'
            ],
            [
                'FK_NAME' => 'CUSTOM_INVOICE_GRID_ORDER',
                'TABLE_NAME' => 'sales_invoice_grid',
                'COLUMN_NAME' => 'order_id',
                'REF_TABLE_NAME' => 'sales_order',
                'REF_COLUMN_NAME' => 'entity_id'
            ],
            [
                'FK_NAME' => 'CUSTOM_SHIPMENT_GRID_SHIPMENT',
                'TABLE_NAME' => 'sales_shipment_grid',
                'COLUMN_NAME' => 'entity_id',
                'REF_TABLE_NAME' => 'sales_shipment',
                'REF_COLUMN_NAME' => 'entity_id'
            ],
            [
                'FK_NAME' => 'CUSTOM_SHIPMENT_GRID_ORDER',
                'TABLE_NAME' => 'sales_shipment_grid',
                'COLUMN_NAME' => 'order_id',
                'REF_TABLE_NAME' => 'sales_order',
                'REF_COLUMN_NAME' => 'entity_id'
            ],
            [
                'FK_NAME' => 'CUSTOM_CREDITMEMO_GRID_CREDITMEMO',
                'TABLE_NAME' => 'sales_creditmemo_grid',
                'COLUMN_NAME' => 'entity_id',
                'REF_TABLE_NAME' => 'sales_creditmemo',
                'REF_COLUMN_NAME' => 'entity_id'
            ],
            [
                'FK_NAME' => 'CUSTOM_CREDITMEMO_GRID_ORDER',
                'TABLE_NAME' => 'sales_creditmemo_grid',
                'COLUMN_NAME' => 'order_id',
                'REF_TABLE_NAME' => 'sales_order',
                'REF_COLUMN_NAME' => 'entity_id'
            ]
        ];

        foreach ($foreignKeys as $foreignKey) {
            $this->customDependencies[$foreignKey['TABLE_NAME']][$foreignKey['REF_TABLE_NAME']][] = $foreignKey;
            $this->customReverseDependencies[$foreignKey['REF_TABLE_NAME']][$foreignKey['TABLE_NAME']][] = $foreignKey;
        }
    }

    /*protected function getForeignKeys($table)
    {
        $select = $this->connection->select()
            ->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE', ['TABLE_NAME', 'COLUMN_NAME', 'REFERENCED_TABLE_NAME', 'REFERENCED_COLUMN_NAME'])
            ->where('REFERENCED_TABLE_SCHEMA = ?', $this->connection->getFo)
        $this->connection->fetchAll()
    }*/

    protected function write($message)
    {
        if ($this->output) {
            $this->output->write($message);
        }
    }

    protected function writeln($message)
    {
        if ($this->output) {
            $this->output->writeln($message);
        }
    }
}
