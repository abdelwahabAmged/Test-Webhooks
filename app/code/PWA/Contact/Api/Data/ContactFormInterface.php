<?php

namespace PWA\Contact\Api\Data;

interface ContactFormInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return ContactFormInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return ContactFormInterface
     */
    public function setEmail($email);

    /**
     * @return string|null
     */
    public function getTelephone();

    /**
     * @param string|null $telephone
     * @return ContactFormInterface
     */
    public function setTelephone($telephone);

    /**
     * @return string
     */
    public function getComment();

    /**
     * @param string $comment
     * @return ContactFormInterface
     */
    public function setComment($comment);
}
