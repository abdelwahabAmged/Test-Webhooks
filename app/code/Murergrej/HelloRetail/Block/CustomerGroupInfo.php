<?php
/**
 * CustomerGroupInfo Block returns the current customer group information and is used to help the HelloRetail Search    .
 *
 * @category    Murergrej
 * @package     Murergrej_HelloRetail
 * @author      Abanoub Youssef <abanoub.youssef@scandiweb.com>
 */
declare(strict_types=1);

namespace Murergrej\HelloRetail\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

class CustomerGroupInfo extends Template
{
    /*
     * Guest ID constant
     */
    public const GUEST_ID = 0;
    /**
     * @var Session $customerSession
     */
    protected Session $customerSession;

    /**
     * @param Template\Context $context
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return Session
     */
    public function getCustomerSession(): Session
    {
        return $this->customerSession;
    }
}
