<?php

namespace PWA\Banner\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;

class BannerImage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('pwa_banner_image', 'image_id');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \PWA\Banner\Model\BannerImage|AbstractModel $object
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $linkField = $this->getIdFieldName();

        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [
                Store::DEFAULT_STORE_ID,
                (int)$object->getStoreId(),
            ];
            $select->join(
                ['pwa_banner_image_store' => $this->getTable('pwa_banner_image_store')],
                $this->getMainTable() . '.' . $this->getIdFieldName() . ' = pwa_banner_image_store.' . $linkField,
                []
            )
                ->where('status = ?', 1)
                ->where('pwa_banner_image_store.store_id IN (?)', $storeIds)
                ->order('pwa_banner_image_store.store_id DESC')
                ->limit(1);
        }

        return $select;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $imageId
     * @return array
     */
    public function lookupStoreIds($imageId)
    {
        $connection = $this->getConnection();

        $linkField = $this->getIdFieldName();

        $select = $connection->select()
            ->from(['its' => $this->getTable('pwa_banner_image_store')], 'store_id')
            ->join(
                ['it' => $this->getMainTable()],
                'its.' . $linkField . ' = it.' . $linkField,
                []
            )
            ->where('it.' . $this->getIdFieldName() . ' = :image_id');

        return $connection->fetchCol($select, ['image_id' => (int)$imageId]);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $linkField = $this->getIdFieldName();

        $connection = $this->getConnection();

        $oldStores = $this->lookupStoreIds((int)$object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }

        $table = $this->getTable('pwa_banner_image_store');

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = [
                $linkField . ' = ?' => (int)$object->getData($linkField),
                'store_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    $linkField => (int)$object->getData($linkField),
                    'store_id' => (int)$storeId
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds((int)$object->getId());
            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }
}
