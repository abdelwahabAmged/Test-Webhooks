<?php
/**
 * @category  Scandiweb
 * @package   Scandiweb\HyvaUi
 * @author    Amr osama <amr.osama@scandiweb.com>
 * @copyright Copyright (c) 2023 Scandiweb, Inc (https://scandiweb.com)
 * @license   http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);
namespace Scandiweb\HyvaUi\Model;

use Magento\Widget\Model\Widget as SourceWidget;
use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\View\FileSystem;
use Magento\Widget\Helper\Conditions;
use Magento\Widget\Model\Config\Data;
/**
 * Class Widget
 * @package Scandiweb\HyvaUi\Model
 */
class Widget extends SourceWidget
{
    /**
     * @var string[]
     */
    protected $reservedChars = ['}', '{'];

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * @param Escaper $escaper
     * @param Data $dataStorage
     * @param Repository $assetRepo
     * @param Source $assetSource
     * @param FileSystem $viewFileSystem
     * @param Conditions $conditionsHelper
     * @param Random $mathRandom
     */
    public function __construct(
        Escaper $escaper,
        Data $dataStorage,
        Repository $assetRepo,
        Source $assetSource,
        FileSystem $viewFileSystem,
        Conditions $conditionsHelper,
        Random $mathRandom
    ) {
        $this->mathRandom = $mathRandom;
        parent::__construct($escaper, $dataStorage, $assetRepo, $assetSource, $viewFileSystem, $conditionsHelper);
    }

    /**
     * Return widget presentation code in WYSIWYG editor
     *
     * @param string $type Widget Type
     * @param array $params Pre-configured Widget Params
     * @param bool $asIs Return result as widget directive(true) or as placeholder image(false)
     * @return string Widget directive ready to parse
    */
    public function getWidgetDeclaration($type, $params = [], $asIs = true): string
    {
        $widget = $this->getConfigAsObject($type);

        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== '';
        });

        $directiveParams = '';
        foreach ($params as $name => $value) {
            // Retrieve default option value if pre-configured
            $directiveParams .= $this->getDirectiveParam($widget, $name, $value);
        }

        $directive = sprintf('{{widget type="%s"%s%s}}', $type, $directiveParams, $this->getWidgetPageVarName($params));

        if ($asIs) {
            return $directive;
        }

        return sprintf(
            '<img id="%s" src="%s" title="%s">',
            $this->idEncode($directive),
            $this->getPlaceholderImageUrl($type),
            $this->escaper->escapeUrl($directive)
        );
    }

    /**
    * Overriden to add custom behavior for Dynamic Rows
    *
    * @param DataObject $widget
    * @param string $name
    * @param string|array $value
    * @return string
    */
	private function getDirectiveParam(DataObject $widget, string $name, $value): string
    {
        if($name === 'slides_data' || $name === 'sections'){
            $value = implode(',', $value);

            return sprintf(" %s='%s'", $name, $value);
        }

        if ($name === 'conditions') {
            $name = 'conditions_encoded';
            $value = $this->conditionsHelper->encode($value);
        } elseif (is_array($value)) {
            $value = implode(',', $value);
        } elseif (trim($value) === '') {
            $parameters = $widget->getParameters();
            if (isset($parameters[$name]) && is_object($parameters[$name])) {
                $value = $parameters[$name]->getValue();
            }
        } else {
            $value = $this->getPreparedValue($value);
        }

        return sprintf(' %s="%s"', $name, $this->escaper->escapeHtmlAttr($value, false));

    }

     /**
     * Get widget page varname
     *
     * @param array $params
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getWidgetPageVarName($params = [])
    {
        $pageVarName = '';
        if (array_key_exists('show_pager', $params) && (bool)$params['show_pager']) {
            $pageVarName = sprintf(
                ' %s="%s"',
                'page_var_name',
                'p' . $this->mathRandom->getRandomString(5, Random::CHARS_LOWERS)
            );
        }
        return $pageVarName;
    }

     /**
     * Returns encoded value if it contains reserved chars
     *
     * @param string $value
     * @return string
     */
    private function getPreparedValue(string $value): string
    {
        $pattern = sprintf('/%s/', implode('|', $this->reservedChars));

        return preg_match($pattern, $value) ? rawurlencode($value) : $value;
    }
}