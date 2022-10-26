<?php

namespace Amasty\BannersLite\Model\Config;
use Magento\Framework\Option\ArrayInterface;

class Display implements ArrayInterface
{
    const BANNER = 0;
    const PRODUCT = 1;

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BANNER,
                'label' => __('Banner')
            ], [
                'value' => self::PRODUCT,
                'label' => __('Product')
            ]
        ];
    }
}
