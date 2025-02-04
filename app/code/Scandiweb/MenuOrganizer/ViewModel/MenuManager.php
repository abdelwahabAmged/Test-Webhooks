<?php

/**
 * @category  Scandiweb
 * @author    Ziad Alnagar <ziad.alnagar@scandiweb.com | info@scandiweb.com>
 * @author    Amr Osama <amr.osama@scandiweb.com | info@scandiweb.com>
 * @copyright Copyright (c) 2023 Scandiweb, Inc (https://scandiweb.com)
 * @license   http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Scandiweb\MenuOrganizer\ViewModel;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Cms\Helper\Page;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManager;
use ScandiPWA\MenuOrganizer\Block\Menu;
use Hyva\Theme\ViewModel\ProductListItem;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Block\Product\AbstractProduct as ProductTemplate;

class MenuManager extends Menu implements ArgumentInterface
{
    /**
     * @var TreeFactory
     */
    protected TreeFactory $treeFactory;

    /**
     * @var string
     */
    protected string $identifier;

    /**
     * @var ProductListItem
     */
    protected ProductListItem $productListItemViewModel;

    /**
     * @var ProductCollectionFactory
     */
    protected ProductCollectionFactory $categoryCollectionFactory;

    /**
     * @inheritDoc
     */
    public function __construct(
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        Page $cmsPageHelper,
        StoreManager $storeManager,
        Registry $registry,
        CollectionFactory $categoryCollectionFactory,
        ProductListItem $productListItemViewModel,
        ProductCollectionFactory $productCollectionFactory
    ) {
        parent::__construct(
            $context,
            $nodeFactory,
            $treeFactory,
            $cmsPageHelper,
            $storeManager,
            $registry,
            $categoryCollectionFactory
        );

        $this->treeFactory = $treeFactory;
        $this->productListItemViewModel = $productListItemViewModel;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param string $identifier
     * @return Node
     */
    public function getMenu(string $identifier): Node
    {
        $this->buildMenu($identifier);

        return $this->_menu;
    }

    /**
     * @param string $identifier
     */
    protected function buildMenu(string $identifier): void
    {
        $this->identifier = $identifier;

        $this->_menu = $this->_nodeFactory->create(
            [
                'data' => ['identifier' => $identifier],
                'idField' => 'root',
                'tree' => $this->treeFactory->create()
            ]
        );

        $this->initMenu();
        $this->_fillMenuTree();
    }

        /**
     * @return bool
     */
    protected function _fillMenuTree()
    {
        $collection = $this->_getMenuItemCollection()
            ->setParentIdOrder()
            ->setPositionOrder();

        if (!$collection->count()) {
            return false;
        }

        $nodes = [];
        $deferredItems = [];
        $nodes[0] = $this->_menu;

        foreach ($collection as $item) {
            if (!isset($nodes[$item->getParentId()])) {
                $deferredItems[] = $item;
                continue;
            }

            /**
             * @var $parentItemNode Node
             */
            $parentItemNode = $nodes[$item->getParentId()];

            $itemNode = $this->_nodeFactory->create(
                [
                    'data' => $item->getData(),
                    'idField' => 'item_id',
                    'tree' => $parentItemNode->getTree(),
                    'parent' => $parentItemNode
                ]
            );

            $nodes[$item->getId()] = $itemNode;
            $parentItemNode->addChild($itemNode);

            if ($categoryId = $item->getCategoryId()) {
                $this->_categoryItemIds[$item->getId()] = $categoryId;
            }
        }

        foreach ($deferredItems as $item) {
            if (!isset($nodes[$item->getParentId()])) {
                continue;
            }

            /**
             * @var $parentItemNode Node
             */
            $parentItemNode = $nodes[$item->getParentId()];

            $itemNode = $this->_nodeFactory->create(
                [
                    'data' => $item->getData(),
                    'idField' => 'item_id',
                    'tree' => $parentItemNode->getTree(),
                    'parent' => $parentItemNode
                ]
            );

            $nodes[$item->getId()] = $itemNode;
            $parentItemNode->addChild($itemNode);

            if ($categoryId = $item->getCategoryId()) {
                $this->_categoryItemIds[$item->getId()] = $categoryId;
            }
        }

        $this->_fillCategoryData($nodes);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function initMenu()
    {
        if ($this->_menuModel) {
            return $this->_menuModel;
        }

        if ($identifier = $this->identifier) {
            $objectManager = ObjectManager::getInstance();

            $collection = $objectManager->create($this->_menuCollectionClass);
            $collection->addFieldToFilter('identifier', $identifier);
            $collection->addStoreFilter($this->_getStoresToFilter());

            $this->_menuModel = $collection->getFirstItem();
        }

        return false;
    }

    /**
     * @param string $identifier
     * @return Collection
     */
    public function getMenuItems(string $identifier): Collection
    {
        $this->buildMenu($identifier);

        return $this->_menu->getChildren();
    }
    public function getFullUrl($item)
    {
        $url = '';

        switch ($item->getUrlType()) {
            case 0:
            default:
                if (!($itemFullUrl = $item->getFullUrl()) && ($itemUrl = $item->getUrl())) {
                    if (strpos($itemUrl, '://') === false) {
                        $itemUrl = $this->_urlBuilder->getDirectUrl($itemUrl != '/' ? ltrim($itemUrl, "/") : '');
                    }

                    $itemUrl .= $item->getUrlAttributes();
                    $item->setFullUrl($itemUrl);
                }
                break;
            case 1:
                $url = $this->_cmsPageHelper->getPageUrl($item->getCmsPageId());
                $url .= $item->getUrlAttributes();
                $item->setFullUrl($url);
                break;
            case 2:
                if (!$itemFullUrl = $item->getFullUrl()) {
                    $category = $item->getCategory();
                    if ($category) {
                        $url = $category->getUrl();
                        $url .= $item->getUrlAttributes();
                    } else {
                        $url = '#';
                    }

                    $item->setFullUrl($url);
                }
                break;
        }

        return $item->getFullUrl();
    }

    public function getImageUrl($item)
    {
        if (!$item->getIcon()) {
            return null;
        }

        return $this->_urlBuilder->getDirectUrl(
            $item->getIcon(),
            ['_type' => UrlInterface::URL_TYPE_MEDIA]
        );
    }

    public function getIdentities(){
        if($this->_menuModel){
            return $this->_menuModel->getIdentities();
        }
    }

    public function renderProductCard($productSku){
        $viewMode         = 'grid';
        $imageDisplayArea = 'category_page_grid';
        $showDescription  = false;
        $templateType     = \Magento\Catalog\Block\Product\ReviewRendererInterface::DEFAULT_VIEW;

        $product = $this->productCollectionFactory->create()->addAttributeToSelect("*")->addAttributeToFilter('sku', $productSku)->getFirstItem();

        $itemRendererBlock = $this->getLayout()->createBlock(ProductTemplate::class)->setTemplate('Magento_Catalog::product/list/item.phtml');
        $itemRendererBlock->setData('product', $product)
        ->setData('view_mode', $viewMode)
        ->setData('show_description', $showDescription)
        ->setData('image_display_area', $imageDisplayArea)
        ->setData('template_type', $templateType)
        ->setData('cache_lifetime', 3600)
        ->setData('cache_tags', $product->getIdentities())
        ->setData('hideDetails', true)
        ->setData('hide_rating_summary', false);

        return $itemRendererBlock->toHtml();
    }
}
