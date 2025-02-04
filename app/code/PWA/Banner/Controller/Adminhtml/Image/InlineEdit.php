<?php

namespace PWA\Banner\Controller\Adminhtml\Image;

use Magento\Backend\App\Action\Context;
use PWA\Banner\Model\BannerImageFactory as BannerImageFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use PWA\Banner\Model\BannerImage;

/**
 * Banner grid inline edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'PWA_Banner::save';

    /**
     * @var \PWA\Banner\Controller\Adminhtml\Image\PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var BannerImageFactory
     */
    protected $bannerImageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param BannerImageFactory $bannerImageFactory
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        BannerImageFactory $bannerImageFactory,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->bannerImageFactory = $bannerImageFactory;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Process the request
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData(
                [
                    'messages' => [__('Please correct the data sent.')],
                    'error' => true,
                ]
            );
        }

        foreach (array_keys($postItems) as $imageId) {
            $banner = $this->bannerImageFactory->create();
            $banner->load($imageId);
            if (!$banner->getId()) {
                $messages[] = 'Banner with ID ' . $imageId . ' does not exist.';
                $error = true;
                continue;
            }
            try {
                $bannerData = $this->filterPost($postItems[$imageId]);
                $this->validatePost($bannerData, $banner, $error, $messages);
                $extendedBannerData = $banner->getData();
                $this->setBannerData($banner, $extendedBannerData, $bannerData);
                $banner->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithBannerId($banner, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithBannerId($banner, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithBannerId(
                    $banner,
                    __('Something went wrong while saving the banner.')
                );
                $error = true;
            }
        }

        return $resultJson->setData(
            [
                'messages' => $messages,
                'error' => $error
            ]
        );
    }

    /**
     * Filtering posted data.
     *
     * @param array $postData
     * @return array
     */
    protected function filterPost($postData = [])
    {
        $bannerData = $this->dataProcessor->filter($postData);
        $bannerData['custom_theme'] = isset($bannerData['custom_theme']) ? $bannerData['custom_theme'] : null;
        $bannerData['custom_root_template'] = isset($bannerData['custom_root_template'])
            ? $bannerData['custom_root_template']
            : null;
        return $bannerData;
    }

    /**
     * Validate post data
     *
     * @param array $bannerData
     * @param \PWA\Banner\Model\BannerImage $banner
     * @param bool $error
     * @param array $messages
     * @return void
     */
    protected function validatePost(array $bannerData, \PWA\Banner\Model\BannerImage $banner, &$error, array &$messages)
    {
        if (!$this->dataProcessor->validateRequireEntry($bannerData)) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithBannerId($banner, $error->getText());
            }
        }
    }

    /**
     * Add banner title to error message
     *
     * @param BannerImage $banner
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBannerId(BannerImage $banner, $errorText)
    {
        return '[Banner ID: ' . $banner->getId() . '] ' . $errorText;
    }

    /**
     * Set banner data
     *
     * @param \PWA\Banner\Model\BannerImage $banner
     * @param array $extendedBannerData
     * @param array $bannerData
     * @return $this
     */
    public function setBannerData(\PWA\Banner\Model\BannerImage $banner, array $extendedBannerData, array $bannerData)
    {
        $banner->setData(array_merge($banner->getData(), $extendedBannerData, $bannerData));
        return $this;
    }
}
