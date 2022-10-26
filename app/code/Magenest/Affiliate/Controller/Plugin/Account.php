<?php


namespace Magenest\Affiliate\Controller\Plugin;

use Closure;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Message\Manager;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magenest\Affiliate\Helper\Data;

/**
 * Class Account
 * @package Magenest\Affiliate\Controller\Plugin
 */
class Account
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var array
     */
    private $allowedActions = [];

    /**
     * @var UrlFactory
     */
    protected $_urlFactory;

    /**
     * @var Http
     */
    protected $response;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    protected $messageManager;

    protected $process = false;

    protected $requiredInfo = [
        'acc_type',
        'bank_no',
        'acc_no',
        'account_name',
        'id_number',
        'issued_by',
        'id_front',
        'id_back'
    ];
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Account constructor.
     * @param Session $customerSession
     * @param Data $helper
     * @param UrlFactory $urlFactory
     * @param Http $response
     * @param ForwardFactory $resultForwardFactory
     * @param Manager $messageManager
     * @param array $allowedActions
     */
    public function __construct(
        Session $customerSession,
        Data $helper,
        UrlFactory $urlFactory,
        Http $response,
        ForwardFactory $resultForwardFactory,
        Manager $messageManager,
        array $allowedActions = []
    ) {
        $this->session = $customerSession;
        $this->allowedActions = $allowedActions;
        $this->helper = $helper;
        $this->_urlFactory = $urlFactory;
        $this->response = $response;
        $this->messageManager = $messageManager;
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Dispatch actions allowed for not authorized users
     *
     * @param ActionInterface $subject
     * @param Closure $proceed
     * @param RequestInterface $request
     *
     * @return mixed
     */
    public function aroundDispatch(
        ActionInterface $subject,
        Closure $proceed,
        RequestInterface $request
    ) {
        $this->request = $request;
        if (!$this->helper->isEnabled()) {
            $resultForward = $this->resultForwardFactory->create();
            $subject->getActionFlag()->set('', ActionInterface::FLAG_NO_DISPATCH, true);

            return $resultForward->forward('noroute');
        }

        $action = strtolower($request->getActionName());
        $patternAffiliate = '/^(' . implode('|', $this->allowedActions) . ')$/i';
        if (!$this->session->authenticate()) {
            $subject->getActionFlag()->set('', ActionInterface::FLAG_NO_DISPATCH, true);
        } elseif (!preg_match($patternAffiliate, $action)) {
            if (!$this->affiliateAuthenticate()) {
                $subject->getActionFlag()->set('', ActionInterface::FLAG_NO_DISPATCH, true);
            }
        } else {
            $this->session->setNoReferer(true);
        }
        $result = $proceed($request);
        $this->session->unsNoReferer(false);

        return $result;
    }

    public function afterDispatch(
        ActionInterface $subject,
        $result
    ) {
        if ($this->request && !$this->request->isAjax()) {
            $this->setNoticeForAffiliate();
            return $result;
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function affiliateAuthenticate()
    {
        $account = $this->helper->getCurrentAffiliate();
        $suffix = '';
        if ($account && $account->getId()) {
            if ($account->isActive()) {
                return true;
            }
        } else {
            $this->session->setBeforeAuthUrl($this->_createUrl()->getUrl('*/*/*', ['_current' => true]));
            $suffix = 'account/signup';
        }
        $this->response->setRedirect($this->_createUrl()->getUrl('affiliate/' . $suffix, ['_current' => true]));

        return false;
    }

    /**
     * @return UrlInterface
     */
    protected function _createUrl()
    {
        return $this->_urlFactory->create();
    }

    protected function setNoticeForAffiliate()
    {
        $account = $this->helper->getCurrentAffiliate();
        foreach ($this->requiredInfo as $info) {
            if (!$account->getData($info) && $account->getData($info) !== "0") {
                $this->messageManager->addNoticeMessage(__('Your information is missing. Please navigate to setting panel to complete your account information.'));
                return;
            }
        }
    }
}
