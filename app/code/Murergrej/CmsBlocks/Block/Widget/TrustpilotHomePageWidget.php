<?php

/**
 * @category    Murergrej
 * @package     Murergrej_CmsBlocks
 * @author      Beshoy Samuel <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\CmsBlocks\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class TrustpilotHomePageWidget extends Template implements BlockInterface
{
    protected $_template = 'Murergrej_CmsBlocks::widget/trustpilot_homepage_widget.phtml';
}
