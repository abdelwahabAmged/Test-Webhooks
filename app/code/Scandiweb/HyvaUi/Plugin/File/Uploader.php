<?php

/**
 * @category Scandiweb
 * @package  Scandiweb\HyvaUi
 * @author   Scandiweb <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Plugin\File;

use Magento\Framework\File\Uploader as SourceUploader;

class Uploader
{
    /**
     * New method to:
     * - Allow uploading SVG file
     *
     * @param SourceUploader $subject
     * @param callable $proceed
     * @param string[] $extensions
     *
     * @return $this
     */
    public function aroundSetAllowedExtensions(SourceUploader $subject, \Closure $proceed, $extensions = [])
    {
        if (!in_array('svg', $extensions)) {
            $extensions[] = 'svg';
        }

        return $proceed($extensions);
    }

    /**
     * New method to:
     * - Allow uploading SVG file
     *
     * @param SourceUploader $subject
     * @param callable $proceed
     * @param string $extension
     *
     * @return boolean
     */
    public function aroundCheckAllowedExtension(SourceUploader $subject, \Closure $proceed, $extension)
    {
        if (strtolower($extension) === 'svg') {
            return true;
        }

        return $proceed($extension);
    }
}
