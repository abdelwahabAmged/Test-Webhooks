<?php
/**
 * Category Widget Block
 *
 * This block is responsible for retrieving and displaying child categories
 * for the current active category on the frontend. It allows categories to be
 * displayed with their names, URLs, and images.
 *
 * @category Murergrej
 * @package  Murergrej_Category
 * @author   Ahmed Elbltagy <info@scandiweb.com>
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

namespace Murergrej\Category\Block\Widget;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Class CategoryWidget
 *
 * This class retrieves the child categories of a specific category, allowing
 * them to be displayed with their respective names, URLs, and brand images.
 */
class CategoryWidget extends Template implements BlockInterface
{
    /**
     * Template for rendering the widget.
     *
     * @var string
     */
    protected $_template = 'Murergrej_Category::widget/category_widget.phtml';

    /**
     * Category Collection Factory to fetch category collections.
     *
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Registry object to retrieve the current category.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Category Repository for accessing category data.
     *
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Constructor
     *
     * Initializes the block and injects necessary dependencies.
     *
     * @param Template\Context   $context
     * @param CollectionFactory  $categoryCollectionFactory
     * @param Registry           $registry
     * @param CategoryRepository $categoryRepository
     * @param array              $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $categoryCollectionFactory,
        Registry $registry,
        CategoryRepository $categoryRepository,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve child categories of the current active category.
     *
     * This method fetches child categories of the current category, filtering by
     * active status and selecting necessary attributes such as name, URL, and image.
     *
     * @return array Array of child categories with 'name', 'url', and 'brand_image' data.
     */
    public function getChildCategories()
    {
        $currentCategory = $this->getCurrentCategory();
        
        if (!$currentCategory) {
            return [];
        }

        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect(['name', 'url_key', 'image']) // 'image' attribute for Category Image
            ->addAttributeToFilter('parent_id', $currentCategory->getId())
            ->addAttributeToFilter('is_active', 1);

        $categoryData = [];
        foreach ($categories as $category) {
            $categoryData[] = [
                'name' => $category->getName(),
                'url' => $category->getUrl(),
                'brand_image' => $this->getCategoryImageUrl($category) // Retrieve Category Image URL
            ];
        }

        return $categoryData;
    }

    /**
     * Retrieve the current active category.
     *
     * This method returns the currently active category from the registry.
     *
     * @return \Magento\Catalog\Model\Category|null Returns the current category or null if not available.
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Get category image URL.
     *
     * This method retrieves the URL of the category image. If no image is available, it returns null.
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string|null The image URL or null if no image is available.
     */
    public function getCategoryImageUrl($category)
    {
        $image = $category->getImage(); // Retrieve the image attribute
        if ($image) {
            return  $image; // Construct the full image URL
        }
        return null; // Return null if no image is available
    }
}
