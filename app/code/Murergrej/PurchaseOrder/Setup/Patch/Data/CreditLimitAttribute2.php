<?php

namespace Murergrej\PurchaseOrder\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Murergrej\PurchaseOrder\Model\Entity\Attribute\Source\CreditLimit;

class CreditLimitAttribute2 implements DataPatchInterface
{
    const ATTRIBUTE_CODE = 'credit_limit';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->updateAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'source_model' => CreditLimit::class,
                'default_value' => CreditLimit::VALUE_NU_KUNDE
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', self::ATTRIBUTE_CODE)->addData([
            'used_in_forms' => [
                'adminhtml_customer'
            ]
        ]);
        $attribute->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            CreditLimitAttribute::class
        ];
    }
}
