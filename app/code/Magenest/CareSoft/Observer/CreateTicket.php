<?php
namespace Magenest\CareSoft\Observer;

use Magenest\CareSoft\Model\CaresoftApi;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

class CreateTicket implements ObserverInterface
{
    /** @var CaresoftApi */
    private $caresoftApi;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param CaresoftApi $caresoftApi
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        CaresoftApi $caresoftApi
    ) {
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->caresoftApi = $caresoftApi;
    }

    /**
	 * @inheritDoc
	 */
	public function execute(Observer $observer)
    {
        if ($this->scopeConfig->isSetFlag(CaresoftApi::STATUS_CONFIG)) {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();
            if ($order->getState() != $order->getOrigData('state') && $order->getState() === Order::STATE_COMPLETE) {
                $groupId = $this->scopeConfig->getValue(CaresoftApi::GROUP_CONFIG);
                $body = [
                    'ticket' => [
                        'ticket_subject' => (string)__("Đơn hàng #%1 đã giao thành công", $order->getIncrementId()),
                        'ticket_comment' => (string)__("Vui lòng liên hệ khách hàng để chăm sóc"),
                        'email'          => $order->getCustomerEmail(),
                        'phone'          => (string)$this->getPhoneNumber($order),
                        'group_id'       => $groupId,
                        'username'       => $order->getCustomerName()
                    ]
                ];

                $orderField = $this->scopeConfig->getValue(CaresoftApi::CUSTOM_FIELD_CONFIG);
                if (!empty($orderField)) {
                    $body['ticket']['custom_fields'] = [
                        ['id' => $orderField, 'value' => $order->getIncrementId()]
                    ];
                }
                $this->caresoftApi->createTicket($body);
            }
        }
	}

    /**
     * @param Order $order
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getPhoneNumber($order)
    {
        if ($order->getCustomerId()) {
            $customer = $this->customerRepository->getById($order->getCustomerId());
            if ($customer->getCustomAttribute('telephone')) {
                return $customer->getCustomAttribute('telephone')->getValue();
            }
        }

        return $order->getShippingAddress()->getTelephone() ?? $order->getBillingAddress()->getTelephone();
    }
}
