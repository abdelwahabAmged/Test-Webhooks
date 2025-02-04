<?php

namespace PWA\Contact\Model;

use Magento\Contact\Model\MailInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use PWA\Contact\Api\ContactSenderInterface;
use PWA\Contact\Api\Data;
use Psr\Log\LoggerInterface;

class ContactSender implements ContactSenderInterface
{
    /**
     * @var MailInterface
     */
    private $mail;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(MailInterface $mail, LoggerInterface $logger)
    {
        $this->mail = $mail;
        $this->logger = $logger;
    }

    public function send(Data\ContactFormInterface $form)
    {
        try {
            $this->sendEmail($this->validateParams($form));
            return true;
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__('An error occurred while processing your form. Please try again later.'));
        }
    }

    /**
     * @param array $post Post data from contact form
     * @return void
     */
    private function sendEmail($post)
    {
        $this->mail->send(
            $post['email'],
            ['data' => new DataObject($post)]
        );
    }

    protected function validateParams(Data\ContactFormInterface $form)
    {
        if (trim($form->getName()) === '') {
            throw new LocalizedException(__('Enter the Name and try again.'));
        }
        if (trim($form->getComment()) === '') {
            throw new LocalizedException(__('Enter the comment and try again.'));
        }
        if (false === \strpos($form->getEmail(), '@')) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }

        return $form->getData();
    }
}
