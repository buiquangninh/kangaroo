<?php

namespace Magenest\Customer\Plugin;

use Magenest\MobileApi\Model\AccountManagement;
use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Controller\ResultFactory;

class LoginViaApp
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var Header
     */
    protected $_httpHeader;

    /**
     * @var AccountManagement
     */
    protected $_accountManagement;

    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * @param AccountManagement $accountManagement
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param Header $header
     */
    public function __construct(
        AccountManagement $accountManagement,
        ResultFactory     $resultFactory,
        RequestInterface  $request,
        Header            $header
    )
    {
        $this->_request           = $request;
        $this->_httpHeader        = $header;
        $this->_resultFactory     = $resultFactory;
        $this->_accountManagement = $accountManagement;
    }

    /**
     * @param LoginPost $subject
     * @param callable $proceed
     */
    public function afterExecute(LoginPost $subject, $result)
    {
        $userAgent = $this->_httpHeader->getHttpUserAgent();
        if (strpos($userAgent, 'FPT-APP-qn1yh4izOsxLX83h') !== false) {
            $login  = $this->_request->getPost('login');
            $accessToken = $this->_accountManagement->getCustomerToken($login['username'], $login['password']);
            if ($accessToken) {
                return $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl("onek://login_callback?token=".$accessToken);
            }
        }
        return $result;
    }
}
