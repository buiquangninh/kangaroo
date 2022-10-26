<?php


namespace Magenest\LalaMove\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;

class SandboxPrefix implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('sk_test')],
            ['value' => 1, 'label' => __('pk_test')],

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
        return [0 => __('sk_test'), 1 => __('pk_test')];
    }
}
