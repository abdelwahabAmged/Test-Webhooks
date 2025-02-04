<?php

/**
 * @category Scandiweb
 * @package  Scandiweb\HyvaUi
 * @author   Scandiweb <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Plugin\Model\Wysiwyg\Images;

use \Magento\Cms\Model\Wysiwyg\Images\Storage as SourceStorage;

class Storage
{
    /**
     * New method to:
     * - Return original image for SVG file thumbnail
     *
     * @param SourceStorage $subject
     * @param callable $proceed
     * @param string $filePath original file path
     * @param bool $checkFile OPTIONAL is it necessary to check file availability
     *
     * @return string|false
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function aroundGetThumbnailPath(
        SourceStorage $subject,
        \Closure $proceed,
        $filePath,
        $checkFile = false
    ) {
        if (file_exists($filePath) && mime_content_type($filePath) === 'image/svg+xml') {
            return $filePath;
        }

        return $proceed($filePath, $checkFile);
    }

    /**
     * New method to:
     * - Disable resizeFile functionality for SVG file
     *
     * @param SourceStorage $subject
     * @param callable $proceed
     * @param string $source Image path to be resized
     * @param bool $keepRatio Keep aspect ratio or not
     *
     * @return bool|string Resized filepath or false if errors were occurred
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function aroundResizeFile(
        SourceStorage $subject,
        \Closure $proceed,
        $source,
        $keepRatio = true
    ) {
        if (file_exists($source) && mime_content_type($source) === 'image/svg+xml') {
            return false;
        }

        return $proceed($source, $keepRatio);
    }
}
