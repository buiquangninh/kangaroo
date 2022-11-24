<?php

namespace Magenest\MomoPay\Model\Config\Source;

class QueryStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    const STATUS_UNSENT = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 2;

    const STATUS_NOT_RECEIVED = 0;
    const STATUS_RECEIVED = 1;

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STATUS_SUCCESS, 'label' => __('Success')],
            ['value' => self::STATUS_FAIL, 'label' => __('Fail')]
        ];
    }
}