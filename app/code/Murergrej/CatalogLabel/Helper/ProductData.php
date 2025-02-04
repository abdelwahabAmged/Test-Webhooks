<?php

declare(strict_types=1);

namespace Murergrej\CatalogLabel\Helper;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Stock\ItemFactory;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResource;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Module\Manager;
use Magento\Framework\Pricing\Helper\Data as PricingDataHelper;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\CatalogLabel\Model\ConfigProvider;
use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;
use Mirasvit\CatalogLabel\Helper\ProductData as MirasvitProductData;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductData extends MirasvitProductData
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var RuleResource
     */
    private RuleResource $ruleResource;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $localDate;

    /**
     * @var ItemFactory
     */
    private ItemFactory $stockItemFactory;

    /**
     * @param RuleResource $ruleResource
     * @param PricingDataHelper $pricingHelper
     * @param RuleFactory $ruleFactoryModel
     * @param Manager $moduleManager
     * @param StoreManagerInterface $storeManager
     * @param ItemFactory $stockItemFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ConfigurableFactory $productTypeConfigurableFactory
     * @param TimezoneInterface $localDate
     * @param StockStateInterface $stockState
     * @param ConfigProvider $configProvider
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param DateTimeFactory $dateTimeFactory
     * @param LoggerInterface $logger
     * @param State $state
     */
    public function __construct(
        RuleResource $ruleResource,
        PricingDataHelper $pricingHelper,
        RuleFactory $ruleFactoryModel,
        Manager $moduleManager,
        StoreManagerInterface $storeManager,
        ItemFactory $stockItemFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConfigurableFactory $productTypeConfigurableFactory,
        TimezoneInterface $localDate,
        StockStateInterface $stockState,
        ConfigProvider $configProvider,
        DateTimeFormatterInterface $dateTimeFormatter,
        DateTimeFactory $dateTimeFactory,
        LoggerInterface $logger,
        State $state
    ) {
        $this->storeManager = $storeManager;
        $this->ruleResource = $ruleResource;
        $this->localDate = $localDate;
        $this->stockItemFactory = $stockItemFactory;

        parent::__construct(
            $ruleResource,
            $pricingHelper,
            $ruleFactoryModel,
            $moduleManager,
            $storeManager,
            $stockItemFactory,
            $searchCriteriaBuilder,
            $productTypeConfigurableFactory,
            $localDate,
            $stockState,
            $configProvider,
            $dateTimeFormatter,
            $dateTimeFactory,
            $logger,
            $state
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDiscountPercent(): float
    {
        $final  = $this->getSpecialPrice();
        $object = $this->getProduct();
        $ruleDiscount = 0;
        $websiteId = $this->storeManager->getStore($object->getStoreId())->getWebsiteId();

        if (!$final || $final <= 0) {
            $rules = $this->ruleResource->getRulesFromProduct(
                $this->localDate->scopeTimeStamp($object->getStoreId()),
                $websiteId,
                $this->storeManager->getWebsite($websiteId)->getDefaultGroup()->getId(),
                $object->getId()
            );

            if ($object->getTypeId() == 'configurable') { // get rules for children products
                $children = $object->getTypeInstance()->getUsedProducts($object);
                $highestDiscount = 0; // Variable to track the highest discount
                $isSpecialPriceActive = false;

                foreach ($children as $child) {
                    if ($child->getSpecialPrice() > 0) {
                        // Calculate the discount percentage for this child product
                        $childPrice = $child->getPrice();
                        $childDiscount = (($childPrice - $child->getSpecialPrice()) / $childPrice) * 100;

                        // Update highest discount if this child's discount is greater
                        if ($childDiscount > $highestDiscount && $child->getStatus() == Status::STATUS_ENABLED) {
                            $highestDiscount = $childDiscount;
                            $isSpecialPriceActive = $this->isSpecialPriceActive($child);
                        }
                    }

                    $rules = array_merge(
                        $rules,
                        $this->ruleResource->getRulesFromProduct(
                            $this->localDate->scopeDate($object->getStoreId())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                            $this->storeManager->getWebsite(true)->getId(),
                            $this->storeManager->getWebsite(true)->getDefaultGroup()->getId(),
                            $child->getId()
                        )
                    );
                }

                if (!$highestDiscount) {
                    return 0;
                } elseif ($isSpecialPriceActive) {
                    if($highestDiscount < 1) {
                        return round($highestDiscount, 1);
                    }
                    return round($highestDiscount);
                } else {
                    return 0;
                }
            }

            if (count($rules)) {
                foreach ($rules as $ruleData) {
                    if ($ruleData['action_operator'] != RuleInterface::DISCOUNT_ACTION_BY_PERCENT) {
                        continue;
                    }

                    if (abs((float)$ruleData['action_amount']) > $ruleDiscount) {
                        $ruleDiscount = abs((float)$ruleData['action_amount']);
                    }
                }
            }
        }

        return $ruleDiscount
            ? round($ruleDiscount, 2)
            : $this->getDiscount(self::DISCOUNT_PERCENT);
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function isSpecialPriceActive(Product $product): bool
    {
        $specialPrice = $product->getSpecialPrice();
        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();
        $currentDate = $this->localDate->date()->format('Y-m-d H:i:s');

        if ($specialPrice && (!$specialFromDate || $specialFromDate <= $currentDate) &&
            (!$specialToDate || $specialToDate >= $currentDate)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $discountType
     * @return float
     */
    private function getDiscount(string $discountType): float
    {
        $result = 0;
        $product   = $this->getProduct();
        $prodPrice = $this->getPrice();
        $final = $this->getFinalPrice();
        $specialPrice = $this->getSpecialPrice();

        //Advanced Pricing -> Special Price in percent
        if ($product->getTypeId() == 'bundle' && $final > 0 && $specialPrice > 0) {
            $prodPrice = ($final * 100) / $specialPrice;
        }

        if ($prodPrice && $final) {
            $result = $prodPrice - $final;

            if ($discountType == self::DISCOUNT_PERCENT) {
                $result = $result / $prodPrice * 100;
            }
        }

        if ($result < 1) {
            return round($result, 1);
        }

        return round($result);
    }

    /**
     * @return float
     */
    public function getFinalPrice(): float
    {
        $product = $this->getProduct();

        if ($product->getTypeId() == 'giftcard') { // EE giftcard product
            return $this->getSpecialPrice() ?: (float)$product->getPrice();
        }

        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();

        if ($product->getTypeId() === 'configurable') {
            $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        }

        return $finalPrice ? (float)$finalPrice : (float)$this->getSpecialPrice();
    }

    /**
     * @return bool
     */
    public function getIsInStock(): bool
    {
        $result = true;
        $object = $this->getProduct();

        // Check for the permanently_oos attribute
        $permanentlyOOS = (int)$object->getData('permanently_oos');

        if ($permanentlyOOS) {
            return false;
        }

        $stockItem = $this->stockItemFactory->create()
            ->load($object->getId(), 'product_id');

        if ($object->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $children = $object->getTypeInstance()
                ->getSelectionsCollection(
                    $object->getTypeInstance()->getOptionsIds($object),
                    $object
                );

            $bundlestock = true;

            if (empty($children)) {
                $bundlestock = false;
            } else {
                foreach ($children as $child) {
                    $childStockItem = $this->stockItemFactory->create()
                        ->load($child->getId(), 'product_id');

                    if ($childStockItem->getIsInStock()) {
                        $bundlestock = false;
                    }
                }
            }

            if (!$bundlestock || !$object->isAvailable()) {
                $result = false;
            }
        } elseif (!$object->isAvailable() || !$stockItem->getIsInStock()) {
            $result = false;
        }

        return $result;
    }
}
