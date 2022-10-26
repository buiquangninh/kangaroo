<?php

namespace Magenest\Customer\Block;

/**
 * Class Navigation
 * @package Magenest\Customer\Block
 */
class OrderNavigation extends AbstractNavigation
{
    /**
     * Search redundant /index and / in url
     */
    const REGEX_URL_PATTERN = '/history|review|wishlist/';

    /**
     * @inheritDoc
     */
    protected function additionalClass()
    {
        return "order";
    }
}
