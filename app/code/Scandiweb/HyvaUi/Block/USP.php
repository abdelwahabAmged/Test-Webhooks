<?php

/**
 * @category Scandiweb
 * @author   Scandiweb <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Block;

use Magento\Framework\View\Element\Template;

class USP extends Template
{
    protected $appearanceTemplateMap = [
        'usp_a' => 'Scandiweb_HyvaUi::scandiweb_ui/usp/usp-a.phtml',
        'usp_b' => 'Scandiweb_HyvaUi::scandiweb_ui/usp/usp-b.phtml',
        'usp_c' => 'Scandiweb_HyvaUi::scandiweb_ui/usp/usp-c.phtml'
    ];

    public function getTemplate()
    {
        $appearance = $this->getData('appearance');
        $template = $this->appearanceTemplateMap[$appearance];

        return $template;
    }
}
