<?php


namespace Magenest\Affiliate\Model\Campaign;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Display
 * @package Magenest\Affiliate\Model\Campaign
 */
class Display implements ArrayInterface
{
    const ALLOW_GUEST = 1;
    const AFFILIATE_MEMBER_ONLY = 2;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::ALLOW_GUEST,
                'label' => __('Allow Guest')
            ],
            [
                'value' => self::AFFILIATE_MEMBER_ONLY,
                'label' => __('Affiliate Member Only')
            ],
        ];

        return $options;
    }
}
