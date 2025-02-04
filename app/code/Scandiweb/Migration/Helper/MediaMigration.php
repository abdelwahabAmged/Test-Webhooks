<?php
/**
 * @category Scandiweb
 * @package  Scandiweb\Migration
 * @author   Scandiweb <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\Migration\Helper;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Module\Dir\Reader as ModuleReader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;

/**
 * Class MediaMigration
 * @package Scandiweb\Migration\Helper
 */
class MediaMigration
{
    /**
     * @param ModuleReader $moduleReader
     * @param Filesystem $fileSystem
     */
    public function __construct(
        protected ModuleReader $moduleReader,
        protected Filesystem $fileSystem
    ) {}

    /**
     * Copies an array of files from a source to a destination media directory.
     *
     * @param array $files
     * @param string $sourceModule Scandiweb_Migration
     * @param ?string $folderPath
     * @return void
     * @throws FileSystemException
     */
    public function copyMediaFiles(array $files, string $sourceModule, ?string $folderPath = null): void
    {
        $sourcePath = $this->getSourceMediaDirectory($sourceModule);
        $destinationPath = $this->getDestinationMediaDirectory($folderPath);

        $rootDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::ROOT);

        $relativeSourcePath = str_replace($rootDirectory->getAbsolutePath(), '', $sourcePath);
        $relativeDestinationPath = str_replace($rootDirectory->getAbsolutePath(), '', $destinationPath);

        foreach ($files as $file) {
            $sourceFile = sprintf('%s%s', $relativeSourcePath, $file);
            if ($rootDirectory->isFile($sourceFile)) {
                $rootDirectory->copyFile($sourceFile, sprintf('%s%s', $relativeDestinationPath, $file));
            }
        }
    }

    /**
     * Gets the directory from which media files are copied.
     *
     * @param $sourceModule
     * @return string
     */
    protected function getSourceMediaDirectory(string $sourceModule): string
    {
        $moduleDir = $this->moduleReader->getModuleDir(Dir::MODULE_VIEW_DIR, $sourceModule);

        return sprintf('%s%s%s%s', $moduleDir, DIRECTORY_SEPARATOR, DirectoryList::MEDIA, DIRECTORY_SEPARATOR);
    }

    /**
     * Gets the directory in which media files are copied to.
     *
     * @param string $folderPath
     * @return string
     * @throws FileSystemException
     */
    protected function getDestinationMediaDirectory(string $folderPath = 'wysiwyg'): string
    {
        if (!$folderPath) {
            return $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        }

        $path = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath();

        return sprintf('%s%s%s', $path, $folderPath, DIRECTORY_SEPARATOR);
    }
}
