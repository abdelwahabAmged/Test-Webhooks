<?php

namespace PWA\Banner\Controller\Adminhtml\Image;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Save banner action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'PWA_Banner::save';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \PWA\Banner\Model\Banner\ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var \PWA\Banner\Model\BannerImageFactory
     */
    private $bannerImageFactory;
    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param \PWA\Banner\Model\BannerImageFactory|null $bannerImageFactory
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        \PWA\Banner\Model\Banner\ImageProcessor $imageProcessor,
        \PWA\Banner\Model\BannerImageFactory $bannerImageFactory = null
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->imageProcessor = $imageProcessor;
        $this->bannerImageFactory = $bannerImageFactory ?: ObjectManager::getInstance()->get(\PWA\Banner\Model\BannerImageFactory::class);
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            if (empty($data['image_id'])) {
                $data['image_id'] = null;
            }

            if(isset($data['filename'])) {
                $this->imageProcessor->save($data['filename']);
                $data['filename'] = $data['filename'][0]['name'];
            } else {
                $data['filename'] = '';
            }

            /** @var \PWA\Banner\Model\BannerImage $model */
            $model = $this->bannerImageFactory->create();

            $id = $this->getRequest()->getParam('image_id');
            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This banner no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $model->setData($data);

            try {
                $this->_eventManager->dispatch(
                    'pwa_banner_prepare_save',
                    ['banner' => $model, 'request' => $this->getRequest()]
                );

                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the banner.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Throwable $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the banner image.'));
            }

            $this->dataPersistor->set('banner', $data);
            return $resultRedirect->setPath('*/*/edit', ['image_id' => $this->getRequest()->getParam('image_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process result redirect
     *
     * @param \PWA\Banner\Model\BannerImage $model
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @param array $data
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newBanner = $this->bannerImageFactory->create(['data' => $data]);
            $newBanner->setId(null);
            $newBanner->setStatus(\PWA\Banner\Model\BannerImage::STATUS_DISABLED);
            $newBanner->save();
            $this->messageManager->addSuccessMessage(__('You duplicated the banner.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'image_id' => $newBanner->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('banner');
        if ($this->getRequest()->getParam('back') == 'continue') {
            return $resultRedirect->setPath('*/*/edit', ['image_id' => $model->getId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
