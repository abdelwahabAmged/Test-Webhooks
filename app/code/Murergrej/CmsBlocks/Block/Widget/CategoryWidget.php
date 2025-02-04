<?php
namespace Murergrej\CmsBlocks\Block\Widget;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class CategoryWidget extends Template implements BlockInterface
{
    protected $_template = 'Murergrej_CmsBlocks::widget/category_widget.phtml';
    protected $categoryCollectionFactory;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param CollectionFactory $categoryCollectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve child categories of the selected category.
     *
     * @return array
     */
    public function getChildCategories()
    {
        $categoryId = $this->getData('category_id');
        if (!$categoryId) {
            return [];
        }

        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect(['name', 'url_key', 'brand_image'])
            ->addAttributeToFilter('parent_id', $categoryId)
            ->addAttributeToFilter('is_active', 1);

        $categoryData = [];
        foreach ($categories as $category) {
            $categoryData[] = [
                'name' => $category->getName(),
                'url' => $category->getUrl(),
                'brand_image' => $category->getBrandImage()
            ];
        }

        return $categoryData;
    }
}
