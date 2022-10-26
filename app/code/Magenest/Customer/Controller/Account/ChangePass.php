<?php

namespace Magenest\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlInterface;

class ChangePass implements AccountInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;
    /**
     * @var UrlInterface
     */
    protected $url;
    protected $resultFactory;

    /**
     * @param UrlInterface $url
     * @param \Magento\Customer\Model\Session $session
     * @param AccountManagementInterface $customerAccountManagement
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        UrlInterface $url,
        \Magento\Customer\Model\Session $session,
        AccountManagementInterface $customerAccountManagement,
        PageFactory $resultPageFactory
    ) {
        $this->resultFactory = $resultFactory;
        $this->url = $url;
        $this->session = $session;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            return $this->resultPageFactory->create();
        } else {
            $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $redirect->setUrl('/customer/account/login');
            return $redirect;
        }
    }

}
