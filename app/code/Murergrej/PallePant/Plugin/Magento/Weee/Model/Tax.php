<?php

  namespace Murergrej\PallePant\Plugin\Magento\Weee\Model;

  use Magento\Catalog\Model\Product;
  use Magento\Store\Model\Website;

  /**
   * Class Tax makes sure that the FPT of the simple products is used.
   * @package Murergrej\PallePant\Plugin\Magento\Weee\Model
   */
  class Tax
  {
      /**
       * @var ProductFactory
       */
      protected $productFactory;

      /**
       * Tax constructor.
       * @param ProductFactory $productFactory
       */
      public function __construct(
          \Magento\Catalog\Model\ProductFactory $productFactory
      )
      {
          $this->productFactory = $productFactory;
      }

      /**
       * @param \Magento\Weee\Model\Tax $taxModel
       * @param callable $callable
       * @param Product $product
       * @param null|false|\Magento\Quote\Model\Quote\Address $shipping
       * @param null|false|\Magento\Quote\Model\Quote\Address $billing
       * @param Website $website
       * @param bool $calculateTax
       * @param bool $round
       * @return mixed
       */
      public function aroundGetProductWeeeAttributes(
          \Magento\Weee\Model\Tax $taxModel,
          callable $callable,
          Product $product,
          $shipping = null,
          $billing = null,
          $website = null,
          $calculateTax = null,
          $round = true)
      {
          $simpleId = false;

          if ($product->getTypeId() === 'configurable') {
              // The FPT is asked for a configurable product. Now we need to check what's the proper FPT of the
              // simple product in question. The first challenge here is to find out which simple product that is.
              // To find this out, we added a rewrite on \Magento\Weee\Model\Total\Quote\Weee that adds the
              // quote item to the product as a reference. This way, we can find out the correct ID of the simple
              // product that is used. Yay!

              /** @var Item $quoteItem */
              $quoteItem = $product->getQuoteItem();
              if ($quoteItem) {
                  $collection = $quoteItem->getQuote()->getItemsCollection();
                  foreach ($collection as $item) {
                      if ($item->getParentItemId() == $quoteItem->getId()) {
                          $simpleId = $item->getProductId();
                      }
                  }
              }
          }

          if ($simpleId) {
              // Perform the method with the simple product instead:
              $simpleProduct = $this->productFactory->create()->load($simpleId);
              $result = $callable($simpleProduct, $shipping, $billing, $website, $calculateTax, $round);
          } else {
              // Perform the actual method:
              $result = $callable($product, $shipping, $billing, $website, $calculateTax, $round);
          }

          return $result;
      }

  }
