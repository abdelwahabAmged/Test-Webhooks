<?php

namespace PWA\Contact\Api;

interface ContactSenderInterface
{
    /**
     * @param Data\ContactFormInterface $form
     * @return bool
     */
    public function send(Data\ContactFormInterface $form);
}
