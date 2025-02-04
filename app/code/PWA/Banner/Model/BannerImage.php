<?php

namespace PWA\Banner\Model;

use Magento\Framework\Model\AbstractModel;
use PWA\Banner\Api\Data\BannerImageInterface;

class BannerImage extends AbstractModel implements BannerImageInterface
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    const BANNER_DESKTOP1 = 0;
    const BANNER_DESKTOP2 = 1;
    const BANNER_MOBILE = 2;

    protected function _construct()
    {
        $this->_init(ResourceModel\BannerImage::class);
    }

    /**
     * @return int
     */
    public function getImageId()
    {
        return $this->getData('image_id');
    }

    /**
     * @param int $imageId
     * @return $this
     */
    public function setImageId($imageId)
    {
        return $this->setData('image_id', $imageId);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData('title', $title);
    }

    /**
     * @return string
     */
    public function getSubtitle()
    {
        return $this->getData('subtitle');
    }

    /**
     * @param string $subtitle
     * @return $this
     */
    public function setSubtitle($subtitle)
    {
        return $this->setData('subtitle', $subtitle);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->getData('filename');
    }

    /**
     * @param string $image
     * @return $this
     */
    public function setFilename($image)
    {
        return $this->setData('filename', $image);
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->getData('link');
    }

    /**
     * @param string $link
     * @return $this
     */
    public function setLink($link)
    {
        return $this->setData('link', $link);
    }

    /**
     * @return int
     */
    public function getBannerId()
    {
        return $this->getData('banner_id');
    }

    /**
     * @param int $bannerId
     * @return $this
     */
    public function setBannerId($bannerId)
    {
        return $this->setData('banner_id', $bannerId);
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->getData('sort_order');
    }

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData('sort_order', $sortOrder);
    }

    /**
     * @return string
     */
    public function getButtonText()
    {
        return $this->getData('button_text');
    }

    /**
     * @param string $buttonText
     * @return $this
     */
    public function setButtonText($buttonText)
    {
        return $this->setData('button_text', $buttonText);
    }

    /**
     * @return string
     */
    public function getBackground()
    {
        return $this->getData('background');
    }

    /**
     * @param string $background
     * @return $this
     */
    public function setBackground($background)
    {
        return $this->setData('background', $background);
    }

    /**
     * @return string
     */
    public function getCreationTime()
    {
        return $this->getData('creation_time');
    }

    /**
     * @param string $creationTime
     * @return $this
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData('creation_time', $creationTime);
    }

    /**
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData('update_time');
    }

    /**
     * @param string $updateTime
     * @return $this
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData('update_time', $updateTime);
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->getData('status');
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }

    public function beforeSave()
    {
        if ($this->hasDataChanges()) {
            $this->setUpdateTime(null);
        }

        return parent::beforeSave();
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public function getAvailableBanners()
    {
        return [
            self::BANNER_DESKTOP1 => __('Desktop Banner 1'),
            self::BANNER_DESKTOP2 => __('Desktop Banner 2'),
            self::BANNER_MOBILE => __('Mobile Banner')
        ];
    }
}
