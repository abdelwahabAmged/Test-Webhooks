<?php

namespace Murergrej\PalletShipping\Model\Entity\Attribute\Source;

use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use Magento\Framework\DB\Select;

class Pallet extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public const VALUE_NO = 0;
    public const VALUE_QUARTER_PALLET = 1;
    public const VALUE_HALF_PALLET = 2;
    public const VALUE_SINGLE_PALLET = 3;
    public const VALUE_PALLET_BY_WEIGHT = 4;

    /**
     * @var AttributeFactory
     */
    protected AttributeFactory $_eavAttrEntity;

    /**
     * @param AttributeFactory $eavAttrEntity
     * @codeCoverageIgnore
     */
    public function __construct(
        AttributeFactory $eavAttrEntity
    ) {
        $this->_eavAttrEntity = $eavAttrEntity;
    }

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Not Pallet'), 'value' => self::VALUE_NO],
                ['label' => __('1/4 Pallet'), 'value' => self::VALUE_QUARTER_PALLET],
                ['label' => __('1/2 Pallet'), 'value' => self::VALUE_HALF_PALLET],
                ['label' => __('Full Pallet'), 'value' => self::VALUE_SINGLE_PALLET],
                ['label' => __('Pallet by Weight'), 'value' => self::VALUE_PALLET_BY_WEIGHT],
            ];
        }
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray(): array
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|int $value
     * @return string|false
     */
    public function getOptionText($value): bool|string
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] === $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns(): array
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length' => 2,
                'nullable' => true,
                'comment' => $attributeCode . ' column',
            ],
        ];
    }

    /**
     * Retrieve Indexes(s) for Flat
     *
     * @return array
     */
    public function getFlatIndexes(): array
    {
        $indexes = [];

        $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = ['type' => 'index', 'fields' => [$this->getAttribute()->getAttributeCode()]];

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Select|null
     */
    public function getFlatUpdateSelect($store): ?Select
    {
        return $this->_eavAttrEntity->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }

    /**
     * Get a text for index option value
     *
     * @param  string|int $value
     * @return string|bool
     */
    public function getIndexOptionText($value): bool|string
    {
        return match ($value) {
            self::VALUE_NO => 'Not Pallet',
            self::VALUE_QUARTER_PALLET => '1/4 Pallet',
            self::VALUE_HALF_PALLET => '1/2 Pallet',
            self::VALUE_SINGLE_PALLET => 'Full Pallet',
            self::VALUE_PALLET_BY_WEIGHT => 'Pallet by Weight',
            default => parent::getIndexOptionText($value),
        };

    }
}
