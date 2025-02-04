<?php

namespace PWA\Banner\Model\File;

class Uploader extends \Magento\MediaStorage\Model\File\Uploader
{
    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileExists ? $this->_file['name'] : '';
    }
}
