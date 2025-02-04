<?php
/**
 * @category Murergrej
 * @package Murergrej_Hyva
 * @author Jorgena Shinjatari info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
*/

namespace Murergrej\Hyva\Model\Entity\Attribute;

use Magento\Eav\Model\Entity\Attribute\Option;

class CustomOption extends Option
{
    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * @param $code
     * @return mixed
     */
    public function setCode($code)
    {
        return $this->setData('code', $code);
    }
}
