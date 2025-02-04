<?php

namespace PWA\FixProductRenderApi\Model;

use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\ProductRenderFactory;
use Magento\Catalog\Model\ProductRenderSearchResultsFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorComposite;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Data\CollectionModifier;
use Magento\Framework\Data\CollectionModifierInterface;

/**
 * Provide product render information (this information should be enough for rendering product on front)
 *
 * Render information provided for one or few products
 */
class CustomerProductRenderList implements \PWA\FixProductRenderApi\Api\CustomerProductRenderListInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductRenderCollectorInterface
     */
    private $productRenderCollectorComposite;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRenderFactory
     */
    private $productRenderFactory;

    /**
     * @var array
     */
    private $productAttributes;

    /**
     * @var CollectionModifierInterface
     */
    private $collectionModifier;

    private $customerRepository;

    /**
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductRenderCollectorComposite $productRenderCollectorComposite
     * @param ProductRenderSearchResultsFactory $searchResultFactory
     * @param ProductRenderFactory $productRenderDtoFactory
     * @param Config $config
     * @param CollectionModifier $collectionModifier
     * @param array $productAttributes
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ProductRenderCollectorComposite $productRenderCollectorComposite,
        ProductRenderSearchResultsFactory $searchResultFactory,
        ProductRenderFactory $productRenderDtoFactory,
        \Magento\Catalog\Model\Config $config,
        CollectionModifier $collectionModifier,
        CustomerRepositoryInterface $customerRepository,
        array $productAttributes
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->productRenderCollectorComposite = $productRenderCollectorComposite;
        $this->searchResultFactory = $searchResultFactory;
        $this->productRenderFactory = $productRenderDtoFactory;
        $this->productAttributes = array_merge($productAttributes, $config->getProductAttributes());
        $this->customerRepository = $customerRepository;
        $this->collectionModifier = $collectionModifier;
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $customerId, $storeId, $currencyCode)
    {
        $customer = $this->customerRepository->getById($customerId);

        $items = [];
        $productCollection = $this->collectionFactory->create();
        $productCollection->addAttributeToSelect($this->productAttributes)
            ->setStoreId($storeId)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents();

        $this->collectionModifier->apply($productCollection);
        $this->collectionProcessor->process($searchCriteria, $productCollection);

        foreach ($productCollection as $item) {
            $productRenderInfo = $this->productRenderFactory->create();
            $productRenderInfo->setStoreId($storeId);
            $productRenderInfo->setCurrencyCode($currencyCode);
            $item->setCustomerGroupId($customer->getGroupId());
            $this->productRenderCollectorComposite->collect($item, $productRenderInfo);
            $items[$item->getId()] = $productRenderInfo;
        }

        $searchResult = $this->searchResultFactory->create();
        $searchResult->setItems($items);
        $searchResult->setTotalCount(count($items));
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
