<?php
/**
 * @category Scandiweb
 * @package  Scandiweb\HyvaUi
 * @author   Scandiweb <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Class FileParser
 * @package Scandiweb\HyvaUi\Helper
 */
class FileParser
{
    public const PATH_TO_MODULE = 'code/Scandiweb/HyvaUi';

    /**
     * @var ReadInterface
     */
    protected $rootDirectory;

    /**
     * FileParser constructor.
     *
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->rootDirectory = $fileSystem->getDirectoryRead(DirectoryList::APP);
    }

    /**
     * Get content from html file
     *
     * @param string $filePath Relative path to the html file from "html" folder
     *
     * @return string
     * @throws FileSystemException
     */
    public function getHtmlContent(string $filePath): string
    {
        return $this->rootDirectory->readFile(
            sprintf('%s/files/%s', self::PATH_TO_MODULE, $filePath)
        );
    }

     /**
     * Return content of json file as associative array
     *
     * @param string $filePath Path from project root
     *
     * @return array
     */
    public function getJSONContent(string $filePath): array
    {
        $data = $this->rootDirectory->readFile($filePath);

        return json_decode($data, true);
    }
}
