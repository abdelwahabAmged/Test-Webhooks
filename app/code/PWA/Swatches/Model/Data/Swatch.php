<?php

namespace PWA\Swatches\Model\Data;

use Magento\Framework\DataObject;
use PWA\Swatches\Api\Data\SwatchInterface;

class Swatch extends DataObject implements SwatchInterface
{
    public function setOptionId($optionId)
    {
        return $this->setData('option_id', $optionId);
    }

    public function getOptionId()
    {
        return $this->getData('option_id');
    }

    public function setType($type)
    {
        return $this->setData('type', $type);
    }

    public function getType()
    {
        return $this->getData('type');
    }

    public function setValue($value)
    {
        return $this->setData('value', $value);
    }

    public function getValue()
    {
        return $this->getData('value');
    }

    public function setImage($image)
    {
        return $this->setData('image', $image);
    }

    public function getImage()
    {
        return $this->getData('image');
    }

    public function setImageMobile($image)
    {
        return $this->setData('image_mobile', $image);
    }

    public function getImageMobile()
    {
        return $this->getData('image_mobile');
    }

    public function setNumber($number)
    {
        return $this->setData('number', $number);
    }

    public function getNumber()
    {
        return $this->getData('number');
    }
}
