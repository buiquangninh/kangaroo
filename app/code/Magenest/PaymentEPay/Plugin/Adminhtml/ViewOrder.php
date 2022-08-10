<?php

namespace Magenest\PaymentEPay\Plugin\Adminhtml;

use Magenest\OrderCancel\Model\Order\Source\AdminCancelReason;
use Magenest\SalesPerson\Model\Order\Source\AssignedToSales;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order;

class ViewOrder
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var AdminCancelReason */
    private $reasonOption;

    /** @var Registry */
    private $registry;
    /**
     * @var AssignedToSales
     */
    protected $assigntoSalesModel;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttonList;

    /**
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @param AssignedToSales $assignedToSalesModel
     * @param Registry $registry
     * @param AdminCancelReason $cancelReason
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList,
        AssignedToSales $assignedToSalesModel,
        Registry $registry,
        AdminCancelReason $cancelReason,
        \Magento\Backend\Model\Auth\Session $authSession,
        SerializerInterface $serializer
    ) {
        $this->buttonList = $buttonList;
        $this->authSession = $authSession;
        $this->assigntoSalesModel = $assignedToSalesModel;
        $this->registry = $registry;
        $this->serializer = $serializer;
        $this->reasonOption = $cancelReason;
    }

    /**
     * Before set layout
     *
     * @param View $subject
     * @param LayoutInterface $layout
     * @return array
     */
    public function beforeSetLayout(View $subject, LayoutInterface $layout)
    {

        $order = $this->getOrder();
        $payment = $order->getPayment();
        $paymentAdditionalData  = $payment->getData('additional_information');
        if (isset($paymentAdditionalData['amountIS'])) {
            if (abs($order->getBaseTotalPaid() - $paymentAdditionalData['amountIS']) != 0 && $order->getData('is_one_part_payment_done') != 1) {
                $this->handleOnePartPaymentButton($subject);
            }
        }



        return [$layout];
    }

    /**
     * Retrieve order model object
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->registry->registry('sales_order');
    }

    /**
     * @param View $subject
     * @param Order $order
     */
    protected function handleOnePartPaymentButton(View $subject)
    {
        $onclickJs = 'jQuery(\'#order_one_part_payment\').om(\'showConfirmOnePartPaymentDialog\', \''
            . __('Is customer has been paid one part payment yet?')
            . '\', \''
            . $subject->getUrl('onepartpayment/payment/onepartpayment')
            . '\');';

        if ($this->authSession->getUser()->getRole()->getId() == 1) {
            $subject->addButton(
                'order_one_part_payment',
                [
                    'label' => __('One Part Payment Confirm Button'),
                    'class' => 'primary action-secondary',
                    'id' => 'order_one_part_payment',
                    'onclick' => $onclickJs,
                    'data_attribute' => [
                        'mage-init' => '{"Magenest_PaymentEPay/js/om": {}}'
                    ]
                ]
            );
        }
    }
}
