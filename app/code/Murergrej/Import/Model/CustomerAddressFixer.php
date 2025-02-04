<?php

namespace Murergrej\Import\Model;

use Magento\Setup\Exception;
use Symfony\Component\Console\Output\OutputInterface;

class CustomerAddressFixer
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

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
     * @var \Zend_Db_Adapter_Pdo_Mysql
     */
    protected $sourceConnection;

    /**
     * @var bool
     */
    protected $test = false;
    protected $testLastInsertId = 1000000;

    /**
     * @var int|null
     */
    protected $startId = null;

    /**
     * @var int|null
     */
    protected $limit = null;

    /**
     * @var string|null
     */
    protected $condition = null;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
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

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getCustomersWithInvalidDefaultAddresses($type)
    {
        switch ($type) {
            case 'billing':
                $addressField = 'default_billing';
                break;
            case 'shipping':
                $addressField = 'default_shipping';
                break;
            default:
                throw new Exception('Invalid address type ' . $type);
        }
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(['c' => $this->resource->getTableName('customer_entity')], 'c.*')
            ->joinInner(['a' => $this->resource->getTableName('customer_address_entity')], 'c.' . $addressField . ' = a.entity_id AND c.entity_id != a.parent_id', '');
        return $connection->fetchAll($select);
    }

    public function execute()
    {
        if ($this->test) {
            $this->_execute();
        } else {
            $this->resource->getConnection()->beginTransaction();
            try {
                $this->_execute();
                $this->resource->getConnection()->commit();
            } catch (\Exception $e) {
                $this->resource->getConnection()->rollBack();
                throw $e;
            }
        }
    }

    protected function _execute()
    {
        $this->sourceConnection = new \Zend_Db_Adapter_Pdo_Mysql($this->connectionSettings);
        $this->connection = $this->resource->getConnection();

        $customers = $this->getCurrentCustomers();
        if (empty($customers)) {
            $this->writeln('No customers found');
            return;
        }
        $this->writeln('Found ' . count($customers) . ' customer.');

        $sourceCustomersMap = $this->getSourceCustomersMap(array_map(function ($customer) {
            return $customer['email'];
        }, $customers));
        $i = 0;
        foreach ($customers as $customer) {
            $i++;
            $this->write($i . '. ' . $customer['entity_id'] . ' ' . $customer['email']);
            $sourceCustomer = $sourceCustomersMap[$customer['email']] ?? null;
            if (!$sourceCustomer) {
                $this->writeln(' source customer not found');
                $this->fixDefaultAddresses($customer, null);
            } else {
                $this->writeln(' source customer ' . $sourceCustomer['entity_id']);
                $this->fixDefaultAddresses($customer, $sourceCustomer);
            }
        }
    }

    protected function fixDefaultAddresses($customer, $sourceCustomer)
    {
        $addresses = $this->getCurrentCustomerAddresses($customer['entity_id']);
        $addresses = $this->toMap($addresses, 'entity_id');

        $sourceAddresses = null;

        $update = [];
        $addressLinks = [
            'default_billing',
            'default_shipping'
        ];

        foreach ($addressLinks as $field) {
            if (!isset($customer[$field])) {
                continue;
            }
            if (isset($addresses[$customer[$field]])) {
                continue;
            }

            $address = null;
            if (isset($sourceCustomer[$field])) {
                if (is_null($sourceAddresses)) {
                    $sourceAddresses = $this->getSourceCustomerAddresses($sourceCustomer['entity_id']);
                    $sourceAddresses = $this->toMap($sourceAddresses, 'entity_id');
                }
                $sourceAddress = $sourceAddresses[$sourceCustomer[$field]] ?? null;
                if ($sourceAddress) {
                    foreach ($addresses as $_address) {
                        if ($this->compareEntities($_address, $sourceAddress, [
                            'entity_id',
                            'parent_id',
                            'created_at',
                            'updated_at',
                            'is_active'
                        ])) {
                            $this->writeln('Source address ' . $sourceAddress['entity_id'] . ' mapped to address ' . $_address['entity_id']);
                            $address = $_address;
                            break;
                        }
                    }
                }
                if (!$address) {
                    $this->writeln('Address for source address ' . $sourceAddress['entity_id'] . ' was not found.');
                }
            }

            if (!$address) {
                foreach ($addresses as $_address) {
                    $address = $_address;
                    break;
                }
            }

            if ($address) {
                $update[$field] = $address['entity_id'];
            } else {
                $update[$field] = null;
            }
        }

        if (!empty($update)) {
            $info = '';
            foreach ($update as $field => $value) {
                $info .= ' ' . $field . ': ' . $customer[$field] . '=>' . (is_null($value) ? 'NULL' : $value);
            }
            $this->writeln($info);
            if (!$this->test) {
                $result = $this->connection->update($this->resource->getTableName('customer_entity'), $update, [
                    'entity_id = ?' => $customer['entity_id']
                ]);
                if ($result != 1) {
                    $this->writeln('WARNING: updated qty is ' . $result);
                }
            }
        }
    }

    protected function compareEntities($entity1, $entity2, $skipFields = [])
    {
        foreach ($entity1 as $field => $value) {
            if (in_array($field, $skipFields)) {
                continue;
            }
            if ($entity2[$field] != $value) {
                // workaround for broken unicode symbols
                if (strpos($value,'?') !== false) {
                    if (strlen($value) != strlen($entity2[$field])) {
                        return false;
                    }
                    $v1 = str_split($value);
                    $v2 = str_split($entity2[$field]);
                    foreach ($v1 as $key => $char) {
                        if ($char === '?') {
                            continue;
                        }
                        if ($char != $v2[$key]) {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @return array
     */
    protected function getCurrentCustomers()
    {
        $select = $this->connection->select()
            ->from($this->resource->getTableName('customer_entity'));
        if ($this->condition) {
            $select->where('(' . $this->condition . ')');
        } else if ($this->startId > 0) {
            $select->where('entity_id >= ?', $this->startId);
        }
        if ($this->limit > 0) {
            $select->limit($this->limit);
        }
        return $this->connection->fetchAll($select);
    }

    protected function getSourceCustomersMap($emails)
    {
        return $this->toMap($this->getSourceCustomers($emails), 'email');
    }

    /**
     * @param array $emails
     * @return array
     */
    protected function getSourceCustomers($emails)
    {
        $select = $this->sourceConnection->select()
            ->from($this->resource->getTableName('customer_entity'))
            ->where('email IN (?)', $emails);
        return $this->sourceConnection->fetchAll($select);
    }

    /**
     * @param int $id
     * @return mixed
     */
    protected function getSourceCustomer($email)
    {
        $select = $this->sourceConnection->select()
            ->from($this->resource->getTableName('customer_entity'))
            ->where('email = ?', $email);
        return $this->sourceConnection->fetchRow($select);
    }

    /**
     * @param array $entities
     * @param string $field
     * @return array
     */
    protected function toMap(array $entities, $field)
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[$entity[$field]] = $entity;
        }
        return $result;
    }

    protected function getCurrentCustomerAddresses($customerId)
    {
        return $this->_getCustomerAddresses($this->connection, $customerId);
    }

    protected function getSourceCustomerAddresses($customerId)
    {
        return $this->_getCustomerAddresses($this->sourceConnection, $customerId);
    }

    /**
     * @param \Zend_Db_Adapter_Pdo_Mysql $connection
     * @param int $customerId
     * @return array
     */
    protected function _getCustomerAddresses($connection, $customerId)
    {
        $select = $connection->select()
            ->from($this->resource->getTableName('customer_address_entity'))
            ->where('parent_id = ?', $customerId);
        return $connection->fetchAll($select);
    }

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
