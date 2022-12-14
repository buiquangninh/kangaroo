<?php

namespace Magenest\Affiliate\Model\Transaction\Action\StoreCredit;

use Magenest\Affiliate\Model\Transaction\AbstractAction;
use Magenest\Affiliate\Model\Transaction\Type;
use Magento\Framework\Phrase;

class SpendingOrder extends AbstractAction
{
    /**
     * @return int
     */
    public function getAmount()
    {
        return (float)$this->getObject()->getAmount();
    }

    /**
     * @return int
     */
    public function getType()
    {
        return Type::STORE_CREDIT;
    }

    /**
     * @param null $transaction
     *
     * @return Phrase
     */
    public function getTitle($transaction = null)
    {
        return $this->getObject()->getTitle() ?? __('Spending kcoin for order');
    }
}
