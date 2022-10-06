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

namespace Magetop\Smtp\Api;

/**
 * Interface for update item information
 * @api
 */
interface CheckoutManagementInterface
{
    /**
     * @param string $cartId
     * @param string $address
     * @param boolean $isOsc
     *
     * @return bool
     */
    public function updateOrder($cartId, $address, $isOsc);
}
