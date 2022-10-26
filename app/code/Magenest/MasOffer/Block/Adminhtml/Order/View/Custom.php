<?php

namespace Magenest\MasOffer\Block\Adminhtml\Order\View;

use Magenest\MasOffer\Helper\MasOffer;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as Context;
use Magento\Sales\Api\OrderRepositoryInterface;
class Custom extends Template
{
    protected OrderRepositoryInterface $orderRepository;
    protected $_request;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
    }
    public function isMasOffer()
    {
        $id = $this->_request->getParam('order_id');
        $order = $this->orderRepository->get($id);
        $data = $order->getData('is_mas_offer');
        return $data;
    }
    public function getOrder(){
        $id = $this->_request->getParam('order_id');
        return $this->orderRepository->get($id);
    }
}
