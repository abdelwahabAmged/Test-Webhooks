<?php

namespace PWA\Contact\Model\Data;

use Magento\Framework\DataObject;
use PWA\Contact\Api\Data\ContactFormInterface;

class ContactForm extends DataObject implements ContactFormInterface
{
    public function getName()
    {
        return $this->getData('name');
    }

    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    public function getEmail()
    {
        return $this->getData('email');
    }

    public function setEmail($email)
    {
        return $this->setData('email', $email);
    }

    public function getTelephone()
    {
        return $this->getData('telephone');
    }

    public function setTelephone($telephone)
    {
        return $this->setData('telephone', $telephone);
    }

    public function getComment()
    {
        return $this->getData('comment');
    }

    public function setComment($comment)
    {
        return $this->setData('comment', $comment);
    }
}
