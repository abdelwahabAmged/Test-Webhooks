<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Catalog
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */
declare(strict_types=1);

namespace Murergrej\Catalog\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\ValidateException;

/**
 * Class CreateDisplayAttributesOnPLPAttribute
 */
class CreateDisplayAttributesOnPLPAttribute implements DataPatchInterface
{
    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected EavSetupFactory $eavSetupFactory,
        protected LoggerInterface $logger
    ) {}

    /**
     * @return void
     * @throws LocalizedException
     * @throws ValidateException
     */
    public function apply(): void
    {
        try {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create();

            $eavSetup->addAttribute(
                Product::ENTITY,
                'display_attributes_on_plp',
                [
                    'group' => 'General',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'class' => '',
                    'label' => 'Display attributes on PLP list view',
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'sort_order' => 12,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'apply_to' => ''
                ]
            );
        } catch (LocalizedException $e) {
            $this->logger->error(
                'LocalizedException while creating display_attributes_on_plp attribute: ' . $e->getMessage()
            );
            throw $e;
        } catch (ValidateException $e) {
            $this->logger->error(
                'ValidateException while creating display_attributes_on_plp attribute: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
