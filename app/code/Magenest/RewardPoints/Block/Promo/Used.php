<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\RewardPoints\Block\Promo;

use Magento\Backend\Block\Context;

/**
 * Coupon codes grid "Used" column renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Used extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    public function __construct(
        \Magenest\CouponCode\Model\ClaimCouponFactory $claimCoupon,
        Context $context,
        array $data = []
    )
    {
        $this->claimCoupon = $claimCoupon;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        try {
            $code = $row->getData('coupon_id');
            $arrCoupon = $this->claimCoupon->create()->load($code,'coupon_id')->getCouponId();
            if ($arrCoupon) {
                return $this->claimCoupon->create()->load($code,'coupon_id')->getCustomerId();
            }
            else
                return '';
        }
        catch (\Exception $e){
            return '';
        }
    }
}
