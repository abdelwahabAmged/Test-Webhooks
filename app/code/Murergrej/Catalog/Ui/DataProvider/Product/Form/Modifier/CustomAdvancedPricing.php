<?php
/**
 * Advanced Pricing modifier for the product form
 *
 * @category   Murergrej
 * @package    Murergrej_Catalog
 * @author     Abanoub.youssef@scandiweb.com
 */
declare(strict_types=1);
namespace Murergrej\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AdvancedPricing as BaseAdvancedPricing;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CurrencySymbolProvider;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Customer\Source\GroupSourceInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;

class CustomAdvancedPricing extends BaseAdvancedPricing
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * CustomAdvancedPricing constructor.
     *
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupManagementInterface $groupManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModuleManager $moduleManager
     * @param Data $directoryHelper
     * @param ArrayManager $arrayManager
     * @param ResourceConnection $resource
     * @param string $scopeName
     * @param GroupSourceInterface|null $customerGroupSource
     * @param CurrencySymbolProvider|null $currencySymbolProvider
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        GroupRepositoryInterface $groupRepository,
        GroupManagementInterface $groupManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ModuleManager $moduleManager,
        Data $directoryHelper,
        ArrayManager $arrayManager,
        ResourceConnection $resource,
        $scopeName = '',
        GroupSourceInterface $customerGroupSource = null,
        ?CurrencySymbolProvider $currencySymbolProvider = null
    ) {
        parent::__construct(
            $locator,
            $storeManager,
            $groupRepository,
            $groupManagement,
            $searchCriteriaBuilder,
            $moduleManager,
            $directoryHelper,
            $arrayManager,
            $scopeName,
            $customerGroupSource,
            $currencySymbolProvider
        );

        // Initialize resource connection
        $this->resource = $resource;
    }

    /**
     * Modify Advanced Pricing Metadata to include custom fields
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        // Call the parent method to get the base structure
        $meta = parent::modifyMeta($meta);

        // Add your custom EAN field to the tier price table
        return $this->addEanColumnToTierPrice($meta);
    }

    /**
     * Add EAN field to the Tier Price table
     *
     * @param array $meta
     * @return array
     */
    private function addEanColumnToTierPrice(array $meta): array
    {
        // Find the path to the tier price dynamic rows
        $tierPricePath = $this->arrayManager->findPath(
            'tier_price',
            $meta,
            null,
            'children'
        );

        if ($tierPricePath) {
            // Add the new 'ean' field to the existing structure
            $meta = $this->arrayManager->merge(
                $tierPricePath . '/children/record/children',
                $meta,
                [
                    'ean' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('EAN'),
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'componentType' => 'field',
                                    'formElement' => 'input',
                                    'dataType' => 'text',
                                    'dataScope' => 'ean',
                                    'sortOrder' => 45,
                                    'additionalClasses' => 'admin__field _required',
                                    'placeholder' => __('EAN'),
                                    'validation' => [
                                        'required-entry' => false
                                    ],
                                    'maxlength' => 255,
                                    'visible' => true,
                                    'default' => '',
                                ],
                            ],
                        ],
                    ]
                ]
            );
        }

        return $meta;
    }

    /**
     * Override modifyData to load EAN values.
     *
     * @param array $data
     * @return array
     */
    /**
     * Override modifyData to load EAN values.
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        $data = parent::modifyData($data);
        $product = $this->locator->getProduct();
        $entityId = $product->getId();
        // Load EAN values from the database
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('catalog_product_entity_tier_price'), ['value_id', 'ean'])
            ->where('entity_id = ?', $entityId);
        $tierPrices = $connection->fetchAll($select);
        // Ensure 'tier_price' is set in $data before looping
        if (isset($data[$entityId]['product']['tier_price']) && is_array($data[$entityId]['product']['tier_price'])) {
            foreach ($data[$entityId]['product']['tier_price'] as &$tierPrice) {
                // Iterate over the fetched tierPrices and match them by 'value_id' (price_id)
                foreach ($tierPrices as $tierPriceData) {
                    if (isset($tierPrice['price_id']) && $tierPrice['price_id'] === $tierPriceData['value_id']) {
                        // Assign the EAN value to the tier_price
                        $tierPrice['ean'] = $tierPriceData['ean'];
                    }
                }
            }
        } else {
            // Initialize 'tier_price' as an empty array if it's not set
            $data[$entityId]['product']['tier_price'] = [];
        }
        return $data;
    }
}
