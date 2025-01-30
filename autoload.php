<?php
/**
 * Register basic autoloader that uses include path
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Hyva\Theme\Model\ViewModelRegistry;
use Hyva\Theme\ViewModel\HeroiconsOutline;
use Hyva\Theme\ViewModel\HeroiconsSolid;
use Hyva\Theme\ViewModel\BlockJsDependencies;
use Hyva\Theme\ViewModel\ProductCompare;
use Hyva\Theme\ViewModel\ProductListItem;
use Hyva\Theme\ViewModel\ProductPage;
use Hyva\Theme\ViewModel\Wishlist;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Helper\Output as CatalogOutputHelper;
use Magento\Catalog\ViewModel\Product\OptionsData as ProductOptionsData;
use Magento\Framework\Escaper;

/**
 * Shortcut constant for the root directory
 */
\define('BP', \dirname(__DIR__));

\define('VENDOR_PATH', BP . '/app/etc/vendor_path.php');

if (!\is_readable(VENDOR_PATH)) {
    throw new \Exception(
        'We can\'t read some files that are required to run the Magento application. '
         . 'This usually means file permissions are set incorrectly.'
    );
}

$vendorAutoload = (
    static function (): ?string {
        $vendorDir = require VENDOR_PATH;

        $vendorAutoload = BP . "/{$vendorDir}/autoload.php";
        if (\is_readable($vendorAutoload)) {
            return $vendorAutoload;
        }

        $vendorAutoload = "{$vendorDir}/autoload.php";
        if (\is_readable($vendorAutoload)) {
            return $vendorAutoload;
        }

        return null;
    }
)();

<?php if ($product->isSaleable()): ?>
    <form method="post"
        action="<?= $escaper->escapeUrl($productViewModel->getAddToCartUrl($product, ['useUencPlaceholder' => true])) ?>"
        class="item product product-item product_addtocart_form card card-interactive flex flex-col <?= $viewIsGrid ? '' : 'md:flex-row' ?>"
        style="background-color: #555555;"
        <?php if ($product->getOptions()): ?>
        enctype="multipart/form-data"
        <?php endif; ?>
    >
        <?= /** @noEscape */ $block->getBlockHtml('formkey') ?>
        <input type="hidden" name="product" value="<?= (int)$productId ?>"/>
        <?php foreach ($options as $optionItem): ?>
        <input type="hidden"
               name="<?= $escaper->escapeHtml($optionItem['name']) ?>"
               value="<?= $escaper->escapeHtml($optionItem['value']) ?>">
        <?php endforeach; ?>
        <?php else: ?>

if ($vendorAutoload === null) {
    throw new \Exception(
        'Vendor autoload is not found. Please run \'composer install\' under application root directory.'
    );
}

$composerAutoloader = include $vendorAutoload;
AutoloaderRegistry::registerAutoloader(new ClassLoaderWrapper($composerAutoloader));
