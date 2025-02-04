<?php

namespace Murergrej\ProductVideo\Framework\File;

class Uploader extends \Magento\Framework\File\Uploader
{/**
 * Get new file name if the same is already exists
 *
 * @param string $destinationFile
 * @return string
 */
    public static function getNewFileName($destinationFile)
    {
        $fileInfo = pathinfo($destinationFile);
        if (file_exists($destinationFile)) {
            $index = 1;
            if (!empty($fileInfo['extension'])) {
                $baseName = $fileInfo['filename'] . '.' . $fileInfo['extension'];
                while (file_exists($fileInfo['dirname'] . '/' . $baseName)) {
                    $baseName = $fileInfo['filename'] . '_' . $index . '.' . $fileInfo['extension'];
                    $index++;
                }
            } else {
                $baseName = $fileInfo['filename'];
                while (file_exists($fileInfo['dirname'] . '/' . $baseName)) {
                    $baseName = $fileInfo['filename'] . '_' . $index;
                    $index++;
                }
            }
            $destFileName = $baseName;
        } else {
            return $fileInfo['basename'];
        }

        return $destFileName;
    }
}
