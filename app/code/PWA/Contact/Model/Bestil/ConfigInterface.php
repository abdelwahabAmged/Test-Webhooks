<?php
namespace PWA\Contact\Model\Bestil;

/**
 * Contact module configuration
 *
 * @api
 * @since 100.2.0
 */
interface ConfigInterface extends \Magento\Contact\Model\ConfigInterface
{
    /**
     * Recipient email config path
     */
    const XML_PATH_BESTIL_EMAIL_RECIPIENT = 'contact/bestil_email/recipient_email';

    /**
     * Sender email config path
     */
    const XML_PATH_BESTIL_EMAIL_SENDER = 'contact/bestil_email/sender_email_identity';

    /**
     * Email template config path
     */
    const XML_PATH_BESTIL_EMAIL_TEMPLATE = 'contact/bestil_email/email_template';

    /**
     * Return email template identifier
     *
     * @return string
     * @since 100.2.0
     */
    public function emailTemplate();

    /**
     * Return email sender address
     *
     * @return string
     * @since 100.2.0
     */
    public function emailSender();

    /**
     * Return email recipient address
     *
     * @return string
     * @since 100.2.0
     */
    public function emailRecipient();
}
