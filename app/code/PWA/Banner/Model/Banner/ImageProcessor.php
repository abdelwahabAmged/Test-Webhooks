<?php

namespace PWA\Banner\Model\Banner;

use Magento\Store\Model\StoreManagerInterface;

class ImageProcessor
{
    /**
     * @var FileInfo
     */
    protected $fileInfo;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    public function __construct(
        FileInfo $fileInfo,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        ImageUploader $imageUploader
    ) {
        $this->fileInfo = $fileInfo;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->imageUploader = $imageUploader;
    }

    public function save($data)
    {
        $fileName = $data[0]['name'];
        if ($this->fileInfo->isExist($fileName)) {
            return $fileName;
        }
        $this->imageUploader->moveFileFromTmp($fileName);

        return $fileName;
    }

    public function createResizedVersions($fileName)
    {
        $absolutePath = $this->fileInfo->getAbsolutePath($fileName);
        $this->resize($absolutePath, $this->fileInfo->getAbsolutePath($fileName, FileInfo::TYPE_RESIZED), 100, 100);
        $this->resize($absolutePath, $this->fileInfo->getAbsolutePath($fileName, FileInfo::TYPE_SLIDER_RESIZED));
    }

    public function delete($fileName)
    {
        $this->unlink($this->fileInfo->getAbsolutePath($fileName, FileInfo::TYPE_ORIGINAL));
        $this->unlink($this->fileInfo->getAbsolutePath($fileName, FileInfo::TYPE_RESIZED));
        $this->unlink($this->fileInfo->getAbsolutePath($fileName, FileInfo::TYPE_SLIDER_RESIZED));
    }

    protected function unlink($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    protected function resize($sourcePath, $destinationPath, $width = null, $height = null)
    {
        if (!file_exists($sourcePath) || file_exists($destinationPath)) return false;

        $imageResize = $this->imageFactory->create();
        $imageResize->open($sourcePath);
        $imageResize->constrainOnly(TRUE);
        $imageResize->keepTransparency(TRUE);
        $imageResize->keepFrame(FALSE);
        $imageResize->keepAspectRatio(TRUE);
        if ($width != null || $height != null) {
            $imageResize->resize($width, $height);
        }
        $imageResize->save($destinationPath);

        return true;
    }
}
