<?php

namespace Magenest\Customer\Block;

class LogoutNavigation extends AbstractNavigation
{
    const REGEX_URL_PATTERN = '/logout/';

    /**
     * @inheritDoc
     */
    protected function additionalClass()
    {
        return 'logout desktop-device';
    }
}
