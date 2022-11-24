<?php

namespace Magenest\MomoPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MomoStatus implements OptionSourceInterface
{
    const STATUS_PAID = 1;
    const STATUS_UNPAID = 0;

    public function getAllOptions()
    {
        return [
            self::STATUS_UNPAID => __("Not Paid"),
            self::STATUS_PAID => __("Paid")
        ];
    }

    public function toOptionArray()
    {
        $allOptions = $this->getAllOptions();
        $result = [];
        foreach ($allOptions as $value => $label) {
            $result [] = [
                'value' => $value,
                'label' => $label
            ];
        }
        array_walk(
            $result,
            function (&$option) {
                $option['__disableTmpl'] = true;
            }
        );

        return $result;
    }
}
