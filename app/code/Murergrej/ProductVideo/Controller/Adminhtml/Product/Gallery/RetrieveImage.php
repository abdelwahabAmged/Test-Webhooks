<?php

namespace Murergrej\ProductVideo\Controller\Adminhtml\Product\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Murergrej\ProductVideo\Framework\File\Uploader;

class RetrieveImage extends \Magento\ProductVideo\Controller\Adminhtml\Product\Gallery\RetrieveImage
{

    /**
     * URI validator
     *
     * @var \Magento\Framework\Validator\ValidatorInterface
     */
    private $protocolValidator;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
     */
    private $extensionValidator;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Image\AdapterFactory $imageAdapterFactory
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     * @param \Magento\MediaStorage\Model\ResourceModel\File\Storage\File $fileUtility
     * @param \Magento\Framework\Validator\ValidatorInterface $protocolValidator
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $extensionValidator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Image\AdapterFactory $imageAdapterFactory,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\MediaStorage\Model\ResourceModel\File\Storage\File $fileUtility,
        \Magento\Framework\Validator\ValidatorInterface $protocolValidator = null,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $extensionValidator = null
    ) {
        parent::__construct($context, $resultRawFactory, $mediaConfig, $fileSystem, $imageAdapterFactory, $curl, $fileUtility, $protocolValidator, $extensionValidator);
        $this->extensionValidator = $extensionValidator
            ?: \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class);
        $this->protocolValidator = $protocolValidator ?:
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Validator\ValidatorInterface::class);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $baseTmpMediaPath = $this->mediaConfig->getBaseTmpMediaPath();
        try {
            $remoteFileUrl = $this->getRequest()->getParam('remote_image');
            $this->validateRemoteFile($remoteFileUrl);
            $localFileName = \Magento\Framework\File\Uploader::getCorrectFileName(basename($remoteFileUrl));
            $localFileName = $this->processLocalFilenameExtension($localFileName);
            $localTmpFileName = Uploader::getDispersionPath($localFileName) . DIRECTORY_SEPARATOR . $localFileName;
            $localFilePath = $baseTmpMediaPath . ($localTmpFileName);
            $localUniqFilePath = $this->appendNewFileName($localFilePath);
            $this->validateRemoteFileExtensions($localUniqFilePath);
            $this->retrieveRemoteImage($remoteFileUrl, $localUniqFilePath);
            $localFileFullPath = $this->appendAbsoluteFileSystemPath($localUniqFilePath);
            $this->imageAdapter->validateUploadFile($localFileFullPath);
            $result = $this->appendResultSaveRemoteImage($localUniqFilePath);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $fileWriter = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
            if (isset($localFileFullPath) && $fileWriter->isExist($localFileFullPath)) {
                $fileWriter->delete($localFileFullPath);
            }
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

    protected function processLocalFilenameExtension($fileName)
    {
        $pathInfo = pathinfo($fileName);
        if (!isset($pathInfo['extension'])) {
            $fileName .= '.jpg';
        }
        return $fileName;
    }

    /**
     * @param string $localFilePath
     * @return string
     */
    protected function appendNewFileName($localFilePath)
    {
        $destinationFile = $this->appendAbsoluteFileSystemPath($localFilePath);
        $fileName = Uploader::getNewFileName($destinationFile);
        $fileInfo = pathinfo($localFilePath);
        return $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Validate remote file
     *
     * @param string $remoteFileUrl
     * @return \Magento\ProductVideo\Controller\Adminhtml\Product\Gallery\RetrieveImage
     * @throws LocalizedException
     *
     */
    private function validateRemoteFile($remoteFileUrl)
    {
        if (!$this->protocolValidator->isValid($remoteFileUrl)) {
            throw new LocalizedException(
                __("Protocol isn't allowed")
            );
        }

        return $this;
    }

    /**
     * Invalidates files that have script extensions.
     *
     * @param string $filePath
     * @throws \Magento\Framework\Exception\ValidatorException
     * @return void
     */
    private function validateRemoteFileExtensions($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!$this->extensionValidator->isValid($extension)) {
            throw new \Magento\Framework\Exception\ValidatorException(__('Disallowed file type.'));
        }
    }
}
