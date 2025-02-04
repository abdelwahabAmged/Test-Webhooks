<?php

namespace Murergrej\Contact\Plugin\Model;

use Magento\Setup\Exception;
use Murergrej\Contact\Model\Confirmation\Config;
use Psr\Log\LoggerInterface;

class Mail
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Murergrej\Contact\Model\Confirmation\Mail
     */
    protected $mail;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Mail constructor.
     * @param Config $config
     * @param \Murergrej\Contact\Model\Confirmation\Mail $mail
     * @param LoggerInterface $logger
     */
    public function __construct(Config $config, \Murergrej\Contact\Model\Confirmation\Mail $mail, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->mail = $mail;
        $this->logger = $logger;
    }

    public function afterSend(\Magento\Contact\Model\MailInterface $subject, $result, $replyTo, array $variables)
    {
        try {
            $this->sendConfirmation($replyTo, $variables);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return $result;
    }

    protected function sendConfirmation($replyTo, array $variables)
    {
        if (!$this->config->isEnabled()) {
            return;
        }
        $this->mail->send($replyTo, $variables);
    }
}
