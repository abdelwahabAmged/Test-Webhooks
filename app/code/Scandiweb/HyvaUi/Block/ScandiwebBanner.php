<?php

/**
 * @category Scandiweb
 * @author   Scandiweb <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Block;

use Magento\Framework\View\Element\Template;

class ScandiwebBanner extends Template
{
    protected $appearanceTemplateMap = [
        'banner_a' => 'Scandiweb_HyvaUi::scandiweb_ui/banner/banner-a.phtml',
        'banner_b' => 'Scandiweb_HyvaUi::scandiweb_ui/banner/banner-b.phtml',
        'banner_c' => 'Scandiweb_HyvaUi::scandiweb_ui/banner/banner-c.phtml',
        'banner_d' => 'Scandiweb_HyvaUi::scandiweb_ui/banner/banner-d.phtml',
        'banner_e' => 'Scandiweb_HyvaUi::scandiweb_ui/banner/banner-e.phtml'
    ];

    public function getTemplate()
    {
        $appearance = $this->getData('appearance');
        $template = $this->appearanceTemplateMap[$appearance];

        return $template;
    }
}
