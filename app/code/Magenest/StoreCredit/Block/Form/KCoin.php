<?php

namespace Magenest\StoreCredit\Block\Form;


class KCoin extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * Cash on delivery template
     *
     * @var string
     */
    protected $_template = 'Magenest_StoreCredit::form/kcoin.phtml';
}
