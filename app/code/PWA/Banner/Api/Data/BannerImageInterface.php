<?php

namespace PWA\Banner\Api\Data;

interface BannerImageInterface
{
    /**
     * @return int
     */
    public function getImageId();

    /**
     * @param int $imageId
     * @return $this
     */
    public function setImageId($imageId);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getSubtitle();

    /**
     * @param string $subtitle
     * @return $this
     */
    public function setSubtitle($subtitle);

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $image
     * @return $this
     */
    public function setFilename($image);

    /**
     * @return string
     */
    public function getLink();

    /**
     * @param string $link
     * @return $this
     */
    public function setLink($link);

    /**
     * @return int
     */
    public function getBannerId();

    /**
     * @param int $bannerId
     * @return $this
     */
    public function setBannerId($bannerId);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * @return string
     */
    public function getButtonText();

    /**
     * @param string $buttonText
     * @return $this
     */
    public function setButtonText($buttonText);

    /**
     * @return string
     */
    public function getBackground();

    /**
     * @param string $background
     * @return $this
     */
    public function setBackground($background);

    /**
     * @return string
     */
    public function getCreationTime();

    /**
     * @param string $creationTime
     * @return $this
     */
    public function setCreationTime($creationTime);

    /**
     * @return string
     */
    public function getUpdateTime();

    /**
     * @param string $updateTime
     * @return $this
     */
    public function setUpdateTime($updateTime);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $isActive
     * @return $this
     */
    public function setStatus($isActive);
}
