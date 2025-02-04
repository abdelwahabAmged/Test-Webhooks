<?php

namespace PWA\Swatches\Api\Data;

interface SwatchInterface
{
    /**
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * @return int
     */
    public function getOptionId();

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return int
     */
    public function getType();

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param string $image
     * @return $this
     */
    public function setImage($image);

    /**
     * @return string|null
     */
    public function getImage();

    /**
     * @param string $image
     * @return $this
     */
    public function setImageMobile($image);

    /**
     * @return string|null
     */
    public function getImageMobile();

    /**
     * @param string $number
     * @return $this
     */
    public function setNumber($number);

    /**
     * @return string|null
     */
    public function getNumber();
}
