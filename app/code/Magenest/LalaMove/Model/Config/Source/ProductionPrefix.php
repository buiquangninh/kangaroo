<?php


namespace Magenest\LalaMove\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;

class ProductionPrefix implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('pk_prod')],
            ['value' => 1, 'label' => __('sk_prod')],

        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        if ($this)
        return [0 => __('pk_prod'), 1 => __('sk_prod')];
    }
}
