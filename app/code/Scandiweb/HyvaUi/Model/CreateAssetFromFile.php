<?php

/**
 * @category Scandiweb
 * @package  Scandiweb\HyvaUi
 * @author   Scandiweb <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Model;

use Magento\MediaGallerySynchronization\Model\CreateAssetFromFile as SourceCreateAssetFromFile;
use Magento\MediaGallerySynchronization\Model\GetContentHash;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGallerySynchronization\Model\Filesystem\GetFileInfo;

class CreateAssetFromFile extends SourceCreateAssetFromFile
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $driver;

    /**
     * @var AssetInterfaceFactory
     */
    private $assetFactory;

    /**
     * @var GetContentHash
     */
    private $getContentHash;

    /**
     * @var GetFileInfo
     */
    private $getFileInfo;

    const DEFAULT_SVG_WIDTH = 300;
    const DEFAULT_SVG_HEIGHT = 150;

    /**
     * @param Filesystem $filesystem
     * @param File $driver
     * @param AssetInterfaceFactory $assetFactory
     * @param GetContentHash $getContentHash
     * @param GetFileInfo $getFileInfo
     */
    public function __construct(
        Filesystem $filesystem,
        File $driver,
        AssetInterfaceFactory $assetFactory,
        GetContentHash $getContentHash,
        GetFileInfo $getFileInfo
    ) {
        parent::__construct(
            $filesystem,
            $driver,
            $assetFactory,
            $getContentHash,
            $getFileInfo
        );

        $this->filesystem = $filesystem;
        $this->assetFactory = $assetFactory;
        $this->getContentHash = $getContentHash;
        $this->getFileInfo = $getFileInfo;
    }

    /**
     * @inheritdoc
     *
     * Overridden to:
     * - Configure get SVG dimension method
     */
    public function execute(string $path): AssetInterface
    {
        $absolutePath = $this->getMediaDirectory()->getAbsolutePath($path);
        $driver = $this->getMediaDirectory()->getDriver();

        if ($driver instanceof Filesystem\ExtendedDriverInterface) {
            $meta = $driver->getMetadata($absolutePath);
        } else {
            /**
             * SPL file info is not compatible with remote storages and must not be used.
             */
            $file = $this->getFileInfo->execute($absolutePath);
            $extension = $file->getExtension();
            $fileContents = $driver->fileGetContents($absolutePath);

            if ($extension === 'svg') {
                $fileXML = simplexml_load_string($fileContents);
                $fileXMLAttributes = $fileXML->attributes();
                $width = (int) $fileXMLAttributes->width;
                $height = (int) $fileXMLAttributes->height;

                if (!$width) {
                    $width = self::DEFAULT_SVG_WIDTH;
                }

                if (!$height) {
                    $height = self::DEFAULT_SVG_HEIGHT;
                }
            } else {
                [$width, $height] = getimagesizefromstring($fileContents);
            }

            $meta = [
                'size' => $file->getSize(),
                'extension' => $extension,
                'basename' => $file->getBasename(),
                'extra' => [
                    'image-width' => $width,
                    'image-height' => $height
                ]
            ];
        }

        return $this->assetFactory->create(
            [
                'id' => null,
                'path' => $path,
                'title' => $meta['basename'] ?? '',
                'width' => $meta['extra']['image-width'] ?? 0,
                'height' => $meta['extra']['image-height'] ?? 0,
                'hash' => $this->getHash($path),
                'size' => $meta['size'] ?? 0,
                'contentType' => sprintf('%s/%s', 'image', $meta['extension'] ?? ''),
                'source' => 'Local'
            ]
        );
    }

    /**
     * Get hash image content.
     *
     * @param string $path
     * @return string
     * @throws FileSystemException
     */
    private function getHash(string $path): string
    {
        return $this->getContentHash->execute($this->getMediaDirectory()->readFile($path));
    }

    /**
     * Retrieve media directory instance with write access
     *
     * @return Filesystem\Directory\WriteInterface
     */
    private function getMediaDirectory(): Filesystem\Directory\WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }
}
