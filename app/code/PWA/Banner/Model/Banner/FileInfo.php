<?php
namespace PWA\Banner\Model\Banner;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Class FileInfo
 *
 * Provides information about requested file
 */
class FileInfo
{
    /**
     * Path in /pub/media directory
     */
    const ORIGINAL_MEDIA_PATH = 'pwa_banner/original';
    const RESIZED_MEDIA_PATH = 'pwa_banner/thumbnail';
    const SLIDER_RESIZED_MEDIA_PATH = 'pwa_banner/pwa_banner';

    const TYPE_ORIGINAL = 0;
    const TYPE_RESIZED = 1;
    const TYPE_SLIDER_RESIZED = 2;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var ReadInterface
     */
    private $baseDirectory;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Filesystem $filesystem
     * @param Mime $mime
     */
    public function __construct(
        Filesystem $filesystem,
        Mime $mime,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->mime = $mime;
        $this->_storeManager = $storeManager;
    }

    /**
     * Get WriteInterface instance
     *
     * @return WriteInterface
     */
    public function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }

    /**
     * Get Base Directory read instance
     *
     * @return ReadInterface
     */
    private function getBaseDirectory()
    {
        if (!isset($this->baseDirectory)) {
            $this->baseDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        }

        return $this->baseDirectory;
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $fileName
     * @return string
     */
    public function getMimeType($fileName, $type = self::TYPE_ORIGINAL)
    {
        $filePath = $this->getFilePath($fileName, $type);
        $absoluteFilePath = $this->getMediaDirectory()->getAbsolutePath($filePath);

        $result = $this->mime->getMimeType($absoluteFilePath);
        return $result;
    }

    /**
     * @param $fileName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAbsolutePath($fileName, $type = self::TYPE_ORIGINAL)
    {
        return $this->mediaDirectory->getAbsolutePath($this->getFilePath($fileName, $type));
    }

    /**
     * @param $fileName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrl($fileName, $type = self::TYPE_ORIGINAL)
    {
        $absoluteFilePath = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $absoluteFilePath .= $this->getFilePath($fileName, $type);
        return $absoluteFilePath;
    }

    /**
     * Get file statistics data
     *
     * @param string $fileName
     * @return array
     */
    public function getStat($fileName, $type = self::TYPE_ORIGINAL)
    {
        $filePath = $this->getFilePath($fileName, $type);

        $result = $this->getMediaDirectory()->stat($filePath);
        return $result;
    }

    /**
     * Check if the file exists
     *
     * @param string $fileName
     * @return bool
     */
    public function isExist($fileName, $type = self::TYPE_ORIGINAL)
    {
        $filePath = $this->getFilePath($fileName, $type);

        $result = $this->getMediaDirectory()->isExist($filePath);
        return $result;
    }

    /**
     * Construct and return file subpath based on filename relative to media directory
     *
     * @param string $fileName
     * @return string
     */
    private function getFilePath($fileName, $type = self::TYPE_ORIGINAL)
    {
        $filePath = ltrim($fileName, '/');

        $mediaDirectoryRelativeSubpath = $this->getMediaDirectoryPathRelativeToBaseDirectoryPath();
        $isFileNameBeginsWithMediaDirectoryPath = $this->isBeginsWithMediaDirectoryPath($fileName);

        // if the file is not using a relative path, it resides in the paracrab/image media directory
        $fileIsInModuleMediaDir = !$isFileNameBeginsWithMediaDirectoryPath;

        if ($fileIsInModuleMediaDir) {
            $filePath = $this->getDirectoryPath($type) . '/' . $filePath;
        } else {
            $filePath = substr($filePath, strlen($mediaDirectoryRelativeSubpath));
        }

        return $filePath;
    }

    private function getDirectoryPath($type)
    {
        switch ($type) {
            case 'original':
            case self::TYPE_ORIGINAL:
            default:
                return self::ORIGINAL_MEDIA_PATH;
            case 'resized':
            case self::TYPE_RESIZED:
                return self::RESIZED_MEDIA_PATH;
            case 'slider_resized':
            case self::TYPE_SLIDER_RESIZED:
                return self::SLIDER_RESIZED_MEDIA_PATH;
        }
    }

    /**
     * Checks for whether $fileName string begins with media directory path
     *
     * @param string $fileName
     * @return bool
     */
    public function isBeginsWithMediaDirectoryPath($fileName)
    {
        $filePath = ltrim($fileName, '/');

        $mediaDirectoryRelativeSubpath = $this->getMediaDirectoryPathRelativeToBaseDirectoryPath();
        $isFileNameBeginsWithMediaDirectoryPath = strpos($filePath, $mediaDirectoryRelativeSubpath) === 0;

        return $isFileNameBeginsWithMediaDirectoryPath;
    }

    /**
     * Get media directory subpath relative to base directory path
     *
     * @return string
     */
    private function getMediaDirectoryPathRelativeToBaseDirectoryPath()
    {
        $baseDirectoryPath = $this->getBaseDirectory()->getAbsolutePath();
        $mediaDirectoryPath = $this->getMediaDirectory()->getAbsolutePath();

        $mediaDirectoryRelativeSubpath = substr($mediaDirectoryPath, strlen($baseDirectoryPath));

        return $mediaDirectoryRelativeSubpath;
    }
}
