<?php

namespace PWA\Product\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class ProductSpecificationsAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributes = [
            'beskrivelse1' => 'Beskrivelse 1',
            'beskrivelse2' => 'Beskrivelse 2',
            'fordele' => 'Fordele',
            'miljovenling' => 'Miljøvenling',
            'anvendelsesomrader' => 'Anvendelsesområder',
            'forbehandling' => 'Forbehandling',
            'blanding_og_forbrug' => 'Blanding og forbrug',
            'anvendelse_vejledning' => 'Anvendelse vejledning',
            'rengoring_og_pleje' => 'Rengøring og pleje',
            'tekniske_doks_og_downloads' => 'Tekniske doks og downloads',
            'certificeringer_og_normner' => 'Certificeringer og normner',
            'maenderabatter' => 'Mænderabatter',
            'tilbehor' => 'Tilbehør',
            'reservedele' => 'Reservedele'
        ];

        $sortOrder = 0;
        foreach ($attributes as $code => $label) {
            $sortOrder += 10;
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $code, [
                'type'     => 'text',
                'label'    => $label,
                'input'    => 'textarea',
                'required' => false,
                'sort_order' => $sortOrder,
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Product specifications',
                'wysiwyg_enabled' => true
            ]);
        }

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

        ];
    }
}
