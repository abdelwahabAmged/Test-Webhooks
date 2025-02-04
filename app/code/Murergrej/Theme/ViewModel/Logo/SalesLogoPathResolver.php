<?php

declare(strict_types=1);

namespace Murergrej\Theme\ViewModel\Logo;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\MediaStorage\Helper\File\Storage\Database;

/**
 * This class provides forward compatibility for Magento versions < 2.4.3
 *
 * @see \Magento\Theme\ViewModel\Block\Html\Header\LogoPathResolver (added in 2.4.3)
 * @see \Magento\Sales\ViewModel\Header\LogoPathResolver (added in 2.4.3)
 */
class SalesLogoPathResolver extends LogoPathResolver
{
    /**
     * @var Registry
     */
    private Registry $coreRegistry;

    /**
     * @param Context $context
     * @param Database $fileStorageHelper
     * @param Registry $coreRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Database $fileStorageHelper,
        Registry $coreRegistry,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $fileStorageHelper, $scopeConfig, $data);
    }

    /**
     * Return logo image path
     *
     * @return string|null
     * @see \Magento\Sales\ViewModel\Header\LogoPathResolver::getPath
     */
    public function getPath(): ?string
    {
        $path = null;
        $storeId = null;
        $order = $this->coreRegistry->registry('current_order');
        if ($order instanceof Order) {
            $storeId = $order->getStoreId();
        }
        $storeLogoPath = $this->_scopeConfig->getValue(
            'sales/identity/logo_html',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($storeLogoPath !== null) {
            $path = 'sales/store/logo_html/' . $storeLogoPath;
        }
        return $path;
    }
}
