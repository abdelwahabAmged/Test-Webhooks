<?php

namespace Murergrej\PalletShipping\Plugin\Model\ShippingMethodManagement;

class PalletTaxPlugin
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $subject
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface[] $result
     * @param int $cartId
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetList(\Magento\Quote\Api\ShippingMethodManagementInterface $subject, $result, $cartId)
    {
        if (empty($result)) {
            return $result;
        }

        $quote = $this->quoteRepository->get($cartId);
        $palletTax = $quote->getPalletTax();
        $basePalletTax = $quote->getBasePalletTax();
        if (!$palletTax) {
            return $result;
        }

        foreach ($result as $item) {
            $item->setAmount($item->getAmount() + $palletTax);
            $item->setBaseAmount($item->getBaseAmount() + $basePalletTax);
        }

        return $result;
    }
}
