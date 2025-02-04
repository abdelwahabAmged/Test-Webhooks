<?php
/**
 * Price model for the custom product type
 *
 * @category   Murergrej
 * @package    Murergrej_Catalog
 * @author     Abanoub.youssef@scandiweb.com
 */
declare(strict_types=1);

namespace Murergrej\Catalog\Model\Product\Type;

use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * @var ProductTierPriceExtensionFactory
     */
    protected $tierPriceExtensionFactory;
    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resource;
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @param RuleFactory $ruleFactory
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param ScopeConfigInterface $config
     * @param ProductTierPriceExtensionFactory $tierPriceExtensionFactory
     * @param ResourceConnection $resource
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        RuleFactory $ruleFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        Session $customerSession,
        ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        ProductTierPriceInterfaceFactory $tierPriceFactory,
        ScopeConfigInterface $config,
        ResourceConnection $resource,
        ProductRepositoryInterface $productRepository,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory
    ) {
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config
        );

        // Use ObjectManager if resource is not injected
        $this->resource = $resource;
        $this->productRepository = $productRepository;
        $this->tierPriceExtensionFactory = $tierPriceExtensionFactory;
    }

    /**
     * Get EAN for the product tier price
     *
     * @param ProductTierPriceInterface $tierPrice
     * @return string[]|null
     */
    public function getEan(ProductTierPriceInterface $tierPrice): ?array
    {
        return $tierPrice->getExtensionAttributes()?->getEan();
    }

    /**
     * Set EAN for the product tier price
     *
     * @param ProductTierPriceInterface $tierPrice
     * @param array $ean
     * @return void
     */
    public function setEan(ProductTierPriceInterface $tierPrice, array $ean): void
    {
        $extensionAttributes = $tierPrice->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->tierPriceExtensionFactory->create();
        }
        $extensionAttributes->setEan($ean);
        $tierPrice->setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get list of product tier prices with EAN values
     *
     * @param Product $product
     * @return array
     */
    public function getTierPrices($product): array
    {
        $prices = [];
        $tierPrices = $this->getExistingPrices($product, 'tier_price');
        $productId = (int)$product->getId();

        if (!empty($tierPrices)) {
            // Fetch EAN values for the tier prices from the database
            $eanData = $this->fetchEanDataForTierPrices($productId);
            foreach ($tierPrices as $price) {
                $tierPrice = $this->prepareTierPrice($price, $eanData);
                $prices[] = $tierPrice;
            }
        }

        return $prices;
    }

    /**
     * Set tier prices for the product
     *
     * @param Product $product
     * @param array|null $tierPrices
     * @return $this
     */
    public function setTierPrices($product, array $tierPrices = null): static
    {
        $Sku = $product->getSku();
        $productId = $this->getProductIdBySku($Sku);

        // null array means leave everything as is
        if ($tierPrices === null) {
            return $this;
        }

        $allGroupsId = $this->getAllCustomerGroupsId();
        $websiteId = $this->getWebsiteForPriceScope();
        // build the new array of tier prices
        $prices = [];
        foreach ($tierPrices as $index => $price) {
            $extensionAttributes = $price->getExtensionAttributes();
            $priceWebsiteId = $websiteId;

            if (isset($extensionAttributes) && is_numeric($extensionAttributes->getWebsiteId())) {
                $priceWebsiteId = (string)$extensionAttributes->getWebsiteId();
            }
            if (isset($extensionAttributes) && is_array($extensionAttributes->getEan())) {
                $priceEan = implode(',', $extensionAttributes->getEan());
            } else {
                $priceEan = null;
            }

            $prices[] = [
                "value_id" => (int)$this->getValueIdForPriceScope($productId)[$index],
                'website_id' => $priceWebsiteId,
                'cust_group' => $price->getCustomerGroupId(),
                'website_price' => $price->getValue(),
                'price' => $price->getValue(),
                'all_groups' => ($price->getCustomerGroupId() === $allGroupsId),
                'price_qty' => $price->getQty(),
                'percentage_value' => $extensionAttributes ? $extensionAttributes->getPercentageValue() : null,
                'ean' => $priceEan
            ];
        }
        $product->setData('tier_price', $prices);
        return $this;
    }

    /**
     * Fetch EAN data for the tier prices from the database.
     *
     * @param int $productId
     * @return array
     */
    protected function fetchEanDataForTierPrices(int $productId): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('catalog_product_entity_tier_price'), ['value_id', 'ean'])
            ->where('entity_id = ?', $productId);

        return $connection->fetchAssoc($select);
    }

    /**
     * Prepare a single tier price object with EAN values.
     *
     * @param array $price
     * @param array $eanData
     * @return ProductTierPriceInterface
     */
    protected function prepareTierPrice(array $price, array $eanData): ProductTierPriceInterface
    {
        $tierPrice = $this->tierPriceFactory->create();
        $extensionAttributes = $this->tierPriceExtensionFactory->create();

        $tierPrice->setCustomerGroupId((int)$price['cust_group']);
        $tierPrice->setValue((float)($price['website_price'] ?? $price['price'] ?? 0));
        $tierPrice->setQty((float)$price['price_qty']);

        if (isset($price['percentage_value'])) {
            $extensionAttributes->setPercentageValue((float)$price['percentage_value']);
        }

        $websiteId = isset($price['website_id']) ? (int)$price['website_id'] : $this->getWebsiteForPriceScope();
        $extensionAttributes->setWebsiteId($websiteId);

        if (isset($price['price_id']) && !empty($eanData[$price['price_id']]['ean'])) {
            $extensionAttributes->setEan(explode(',', $eanData[$price['price_id']]['ean']));
        } else {
            $extensionAttributes->setEan([]);
        }
        $tierPrice->setExtensionAttributes($extensionAttributes);

        return $tierPrice;
    }

    /**
     * Get value_ids for the existing tier prices based on the product ID.
     *
     * @param int $productId
     * @return array|null
     */
    protected function getValueIdForPriceScope(int $productId): ?array
    {
        $connection = $this->resource->getConnection();

        // Query to find all tier price value_ids for the given productId
        $select = $connection->select()
            ->from($this->resource->getTableName('catalog_product_entity_tier_price'), 'value_id')
            ->where('entity_id = ?', $productId);

        // Fetch all value_ids
        $valueIds = $connection->fetchAll($select);

        // Return the array of value_ids or null if no data found
        return !empty($valueIds) ? array_column($valueIds, 'value_id') : null;
    }

    /**
     * Retrieve the product ID by SKU.
     *
     * @param string $sku
     * @return int|null
     */
    public function getProductIdBySku(string $sku): ?int
    {
        try {
            return (int)$this->productRepository->get($sku)->getId();
        } catch (NoSuchEntityException $e) {
            // Handle the case where the product doesn't exist
            return null;
        }
    }
}
