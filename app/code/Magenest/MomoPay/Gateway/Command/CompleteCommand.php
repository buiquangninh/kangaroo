<?php

namespace Magenest\MomoPay\Gateway\Command;

use Magenest\MomoPay\Helper\MomoHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;

class CompleteCommand implements \Magento\Payment\Gateway\CommandInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;
    /**
     * @var Order\Email\Sender\OrderSender
     */
    protected $orderSender;
    /**
     * @var MomoHelper
     */
    protected $helper;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * CompleteCommand constructor.
     * @param StoreManagerInterface $storeManager
     * @param InvoiceSender $invoiceSender
     * @param InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param Order\Email\Sender\OrderSender $orderSender
     * @param MomoHelper $helper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        InvoiceSender $invoiceSender,
        InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        MomoHelper $helper
    ) {
        $this->storeManager = $storeManager;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->orderSender = $orderSender;
        $this->helper = $helper;
    }

    /**
     * @param array $commandSubject
     * @return \Magento\Payment\Gateway\Command\ResultInterface|void|null
     * @throws LocalizedException
     */
    public function execute(array $commandSubject)
    {
        /** @var Order $order */
        if (isset($commandSubject['order'])) {
            $order = $commandSubject['order'];
        }

        if (!$order || !$order->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
        }
        try {
            $this->createInvoice($order);
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
            $order->save();

            if ($order->getEmailSent() == null) {
                $this->orderSender->send($order);
                $this->helper->debug(__("Send Email at %1", date('Y-m-d H:i:s', time())));
            } else {
                $this->helper->debug(__("Email has been sent"));
            }
        } catch (\Exception $e) {
            $this->helper->debug(__('Command Complete error %1', $e->getMessage()));
        }
    }

    /**
     * @param Order $order
     * @throws LocalizedException
     */
    private function createInvoice($order)
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $transactionSave = $this->transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();

            $this->invoiceSender->send($invoice);
        }
    }
}