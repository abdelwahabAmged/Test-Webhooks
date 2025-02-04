<?php

namespace PWA\Banner\Model\Banner;

use PWA\Banner\Model\Banner\FileInfo;
use PWA\Banner\Model\BannerImage;
use PWA\Banner\Model\ResourceModel\BannerImage\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var \PWA\Banner\Model\ResourceModel\BannerImage\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var FileInfo
     */
    protected $fileInfo;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var AuthorizationInterface
     */
    private $auth;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $bannerCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     * @param AuthorizationInterface|null $auth
     * @param RequestInterface|null $request
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $bannerCollectionFactory,
        DataPersistorInterface $dataPersistor,
        FileInfo $fileInfo,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null,
        ?AuthorizationInterface $auth = null,
        ?RequestInterface $request = null
    ) {
        $this->collection = $bannerCollectionFactory->create();
        $this->collectionFactory = $bannerCollectionFactory;
        $this->dataPersistor = $dataPersistor;
        $this->fileInfo = $fileInfo;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->auth = $auth ?? ObjectManager::getInstance()->get(AuthorizationInterface::class);
        $this->meta = $this->prepareMeta($this->meta);
        $this->request = $request ?? ObjectManager::getInstance()->get(RequestInterface::class);
    }

    /**
     * Find requested banner.
     *
     * @return BannerImage|null
     */
    private function findCurrentBanner(): ?BannerImage
    {
        if ($this->getRequestFieldName() && ($imageId = (int)$this->request->getParam($this->getRequestFieldName()))) {
            //Loading data for the collection.
            $this->getData();
            return $this->collection->getItemById($imageId);
        }

        return null;
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->getCollection()->getItems();
        /** @var $banner \PWA\Banner\Model\BannerImage */
        foreach ($items as $banner) {
            $this->loadedData[$banner->getId()] = $banner->getData();

            $fileName = $banner->getFilename();
            if ($fileName !== '' && $this->fileInfo->isExist($fileName)) {
                $stat = $this->fileInfo->getStat($fileName);
                $image = [
                    [
                        'name' => basename($fileName),
                        'url' => $this->fileInfo->getUrl($fileName),
                        'size' => isset($stat) ? $stat['size'] : 0,
                        'type' => $this->fileInfo->getMimeType($fileName)
                    ]
                ];

                $this->loadedData[$banner->getId()]['filename'] = $image;
            }
        }

        $data = $this->dataPersistor->get('banner');
        if (!empty($data)) {
            $banner = $this->collection->getNewEmptyItem();
            $banner->setData($data);
            $this->loadedData[$banner->getId()] = $banner->getData();
            $this->dataPersistor->clear('banner');
        }

        return $this->loadedData;
    }
}
