<?php
/**
 * @category    Murergrej
 * @package     Murergrej_Catalog
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\Catalog\Helper;

/**
 * Catalog data helper
 */
class Data
{
    /**
     * @var array
     */
    private $categoryPath = [];

    /**
     * Return current category path or get it from current category
     *
     * Creating array of categories|product paths for breadcrumbs
     *
     * @return array
     */
    public function getBreadcrumbPathFromCategory($category = null)
    {
        if (!$this->categoryPath) {
            $path = [];

            if ($category) {
                $pathInStore = $category->getPathInStore();
                $pathIds = array_reverse(explode(',', $pathInStore));
                $categories = $category->getParentCategories(false);

                foreach ($pathIds as $categoryId) {
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        $path['category' . $categoryId] = [
                            'label' => rtrim($categories[$categoryId]->getName())
                        ];
                    }
                }
            }

            $this->categoryPath = $path;
        }

        return $this->categoryPath;
    }
}
