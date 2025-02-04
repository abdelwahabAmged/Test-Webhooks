<?php

namespace Murergrej\CmsBlocks\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;

/**
 * Class MigrateCmsImages
 * 
 * This class handles the migration of CMS images during the setup process.
 */
class MigrateCmsImages implements DataPatchInterface
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MigrateCmsImages constructor.
     * 
     * @param File $file
     * @param DirectoryList $directoryList
     * @param LoggerInterface $logger
     */
    public function __construct(
        File $file,
        DirectoryList $directoryList,
        LoggerInterface $logger
    ) {
        $this->file = $file;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
    }

    /**
     * Apply the patch to migrate CMS images from the source directory to the destination directory.
     * 
     * @return $this
     */
    public function apply()
    {
        // Updated source directory
        $sourceDir = __DIR__ . '/../../../images';
        
        // Corrected destination directory path
        $destinationDir = $this->directoryList->getPath('media') . '/wysiwyg/';

        // Ensure the destination directory exists
        if (!$this->file->fileExists($destinationDir, false)) {
            $this->file->mkdir($destinationDir, 0775);
        }

        // Copy each file from source to destination
        $files = scandir($sourceDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $sourceFile = $sourceDir . '/' . $file;
                $destinationFile = $destinationDir . '/' . $file;
                
                if ($this->file->fileExists($sourceFile)) {
                    $this->file->cp($sourceFile, $destinationFile);
                    $this->logger->info("File $file copied to $destinationFile");
                } else {
                    $this->logger->warning("File $file does not exist in $sourceDir");
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve the list of dependencies for this patch.
     * 
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Retrieve the list of aliases for this patch.
     * 
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
