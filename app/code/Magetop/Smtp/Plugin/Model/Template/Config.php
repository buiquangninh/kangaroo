<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Smtp
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Smtp\Plugin\Model\Template;

use Magento\Email\Model\Template\Config as EmailConfig;

/**
 * Class Config
 * @package Magetop\Smtp\Plugin\Model\Template
 */
class Config
{
    /**
     * @param EmailConfig $subject
     * @param array $templates
     *
     * @return array
     */
    public function afterGetAvailableTemplates(EmailConfig $subject, $templates)
    {
        $key = array_search('mpsmtp_abandoned_cart_email_template', array_column($templates, 'value'));
        unset($templates[$key]);

        return $templates;
    }
}
