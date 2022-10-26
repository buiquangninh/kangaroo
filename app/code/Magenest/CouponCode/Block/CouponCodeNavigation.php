<?php

namespace Magenest\CouponCode\Block;

use Magenest\Customer\Block\AbstractNavigation;

class CouponCodeNavigation extends AbstractNavigation
{
    /**
     * Search redundant /index and / in url
     */
    const REGEX_URL_PATTERN = '/voucher/';

    /**
     * @inheritDoc
     */
    protected function additionalClass()
    {
        return "voucher";
    }
}
