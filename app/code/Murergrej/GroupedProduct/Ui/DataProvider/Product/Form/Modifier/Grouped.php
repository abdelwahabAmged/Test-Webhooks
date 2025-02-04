<?php

namespace Murergrej\GroupedProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Form;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\DynamicRows;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\GroupedProduct\Model\Product\Link\CollectionProvider\Grouped as GroupedProducts;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;

/**
 * Data provider for Grouped products
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grouped extends AbstractModifier
{
    const GROUP_GROUPED = \Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier\Grouped::GROUP_GROUPED;
    const LINK_TYPE = \Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier\Grouped::LINK_TYPE;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var GroupedProducts
     */
    private $groupedProducts;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ImageHelper $imageHelper
     * @param Status $status
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param CurrencyInterface $localeCurrency
     * @param array $uiComponentsConfig
     * @param GroupedProducts $groupedProducts
     * @param \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory|null $productLinkFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ProductLinkRepositoryInterface $productLinkRepository,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        Status $status,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CurrencyInterface $localeCurrency,
        array $uiComponentsConfig = [],
        GroupedProducts $groupedProducts = null,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory = null
    ) {
        $this->locator = $locator;
        $this->groupedProducts = $groupedProducts ?: ObjectManager::getInstance()->get(
            \Magento\GroupedProduct\Model\Product\Link\CollectionProvider\Grouped::class
        );
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->locator->getProduct();
        $modelId = $product->getId();
        if ($modelId) {
            $linkedItems = $this->groupedProducts->getLinkedProducts($product);
            foreach ($data[$product->getId()]['links'][self::LINK_TYPE] as &$link) {
                $linkItem = null;
                foreach ($linkedItems as $_linkItem) {
                    if ($_linkItem->getId() == $link['id']) {
                        $linkItem = $_linkItem;
                        break;
                    }
                }
                if (!$linkItem) {
                    continue;
                }
                if ($linkItem->getData('link_price')) {
                    $link['price'] = (float)$linkItem->getData('link_price');
                } else {
                    $link['price'] = (float)$linkItem->getPrice();
                }
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getProduct()->getTypeId() === GroupedProductType::TYPE_CODE) {
            if (isset($meta[static::GROUP_GROUPED]['children'][self::LINK_TYPE])) {
                $meta[static::GROUP_GROUPED]['children'][self::LINK_TYPE]['arguments']['data']['config']['map']['price'] = 'price_value';
            }
            if (isset($meta[static::GROUP_GROUPED]['children'][self::LINK_TYPE]['children']['record']['children']['price'])) {
                $price =& $meta[static::GROUP_GROUPED]['children'][self::LINK_TYPE]['children']['record']['children']['price'];
                unset($price['arguments']['data']['config']['elementTmpl']);
                $price['arguments']['data']['config']['dataType'] = 'number';
                $price['arguments']['data']['config']['fit'] = true;
                $price['arguments']['data']['config']['additionalClasses'] = 'admin__field-small';
                $price['arguments']['data']['config']['validation'] = [
                    'validate-number' => true
                ];
            }
        }
        return $meta;
    }
}
