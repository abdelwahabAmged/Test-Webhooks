<?php

/**
 * Register Controller
 *
 * Handles customer registration including custom attributes.
 *
 * @category    Murergrej
 * @package     Murergrej_Checkout
 * @author      Abanoub Youssef <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\Checkout\Controller\Account;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\RequestInterface;

class Register implements HttpPostActionInterface
{
    /**
     * @var CustomerInterfaceFactory
     */
    protected CustomerInterfaceFactory $customerFactory;

    /**
     * @var FormKeyValidator
     */
    protected FormKeyValidator $formKeyValidator;

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $redirectFactory;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var AccountManagementInterface
     */
    protected AccountManagementInterface $accountManagement;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * Register constructor.
     *
     * @param Context $context
     * @param CustomerInterfaceFactory $customerFactory
     * @param FormKeyValidator $formKeyValidator
     * @param RedirectFactory $redirectFactory
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $messageManager
     * @param AccountManagementInterface $accountManagement
     * @param RequestInterface $request
     */
    public function __construct(
        Context $context,
        CustomerInterfaceFactory $customerFactory,
        FormKeyValidator $formKeyValidator,
        RedirectFactory $redirectFactory,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager,
        AccountManagementInterface $accountManagement,
        RequestInterface $request,
    ) {
        $this->customerFactory = $customerFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->redirectFactory = $redirectFactory;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->accountManagement = $accountManagement;
        $this->request = $request;
    }

    /**
     * Executes the registration logic, including validation and saving custom attributes.
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->redirectFactory->create();
        $postData = $this->request->getParams();

        // Validate form key
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(__('Invalid form key.'));
            return $resultRedirect->setPath('checkout/onepage/success');
        }

        // Validate required fields
        if (!$this->validateData($postData)) {
            $this->messageManager->addErrorMessage(__('Please fill in all required fields correctly.'));
            return $resultRedirect->setPath('checkout/onepage/success');
        }

        try {
            // Create and populate customer
            $customer = $this->customerFactory->create();
            $customer->setEmail($postData['email'])
                ->setFirstname($postData['name'])
                ->setLastname($postData['surname'])
                ->setStoreId($this->storeManager->getStore()->getId())
                ->setWebsiteId($this->storeManager->getStore()->getWebsiteId())
                ->setCustomAttribute('mp_sms_telephone', $postData['telephone']);

            // Set password and create account
            $password = $postData['password'];

            $this->accountManagement->createAccount($customer, $password);

            $this->messageManager->addSuccessMessage(__('Your account has been created.'));

            return $resultRedirect->setPath('customer/account/login');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t create your account right now.'));
        }

        return $resultRedirect->setPath('checkout/onepage/success');
    }

    /**
     * Validates the submitted registration data.
     *
     * @param array $data
     * @return bool
     */
    private function validateData(array $data): bool
    {
        return !empty($data['name']) && !empty($data['surname']) && !empty($data['email']) && !empty($data['password']) &&
            ($data['password'] === $data['password_confirmation']);
    }
}
