<?php

namespace Magenest\MomoPay\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ResultInterface;

class OrderValidator extends AbstractValidator
{

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject)
    {
        if (isset($validationSubject[self::RESPONSE][self::RESPONSE])) {
            $this->_validationSubject = $validationSubject[self::RESPONSE][self::RESPONSE];
        } else {
            $this->_validationSubject = $validationSubject[self::RESPONSE];
        }

        if ($this->validateResult() && $this->validatePayUrl()) {
            return $this->createResult(true, []);
        }

        $errorMsg[] = $this->_validationSubject->getMessage();
        return $this->createResult(false, $errorMsg);
    }

    /**
     * @return bool
     */
    protected function validateResult()
    {
        return $this->_validationSubject->getResultCode() == 0;
    }

    /**
     * @return bool
     */
    protected function validatePayUrl()
    {
        return !empty($this->_validationSubject->getPayUrl());
    }

}