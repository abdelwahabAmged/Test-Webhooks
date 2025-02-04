<?php

declare(strict_types=1);

namespace Murergrej\Theme\ViewModel\Logo;

use Magento\Config\Model\Config\Backend\Image\Logo;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Hyva\Theme\ViewModel\Logo\LogoPathResolver as SourceLogoPathResolver;

class LogoPathResolver extends SourceLogoPathResolver
{
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param Context $context
     * @param Database $fileStorageHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Database $fileStorageHelper,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context, $fileStorageHelper, $data);
    }

    /**
     * @param string|null $logoFile
     * @return mixed|string
     */
    public function getMobileLogoSrc(?string $logoFile = null)
    {
        if (empty($this->_data['logo_src_mobile'])) {
            $path = null;
            $storeLogoPath = $this->scopeConfig->getValue(
                'design/header/logo_src_mobile',
                ScopeInterface::SCOPE_STORE
            );

            if ($storeLogoPath !== null) {
                $path = Logo::UPLOAD_DIR . '/' . $storeLogoPath;
            }

            if ($path !== null && $this->_isFile($path)) {
                $url = $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $path;
            } elseif ($logoFile) {
                $url = $this->getViewFileUrl($logoFile);
            } else {
                // Default to a specific mobile logo if one exists or fallback to the regular logo
                $url = $this->getViewFileUrl('images/logo.svg');
            }
            $this->_data['logo_src_mobile'] = $url;
        }

        return $this->_data['logo_src_mobile'];
    }
}
