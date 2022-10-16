<?php

namespace Magenest\MomoPay\Controller\Payment;

use Magenest\MomoPay\Helper\MomoHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

abstract class AbstractAction implements \Magento\Framework\App\ActionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var MomoHelper
     */
    protected $helper;
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var OrderResource
     */
    protected $orderResource;
    /**
     * @var \Magenest\MomoPay\Gateway\Command\CompleteCommand
     */
    protected $completeCommand;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * AbstractAction constructor.
     * @param RequestInterface $request
     * @param MomoHelper $helper
     * @param OrderFactory $orderFactory
     * @param OrderResource $orderResource
     * @param RedirectFactory $redirectFactory
     * @param ResponseInterface $response
     * @param ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magenest\MomoPay\Gateway\Command\CompleteCommand $completeCommand
     */
    public function __construct(
        RequestInterface $request,
        MomoHelper $helper,
        OrderFactory $orderFactory,
        OrderResource $orderResource,
        RedirectFactory $redirectFactory,
        ResponseInterface $response,
        ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magenest\MomoPay\Gateway\Command\CompleteCommand $completeCommand
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->orderResource = $orderResource;
        $this->completeCommand = $completeCommand;
        $this->response = $response;
    }
}
