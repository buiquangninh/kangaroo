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

namespace Magetop\Smtp\Block\Adminhtml\AbandonedCart;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Class Edit
 * @package Magetop\Smtp\Block\Adminhtml\AbandonedCart
 */
class Edit extends Container
{
    protected function _construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'Magetop_Smtp';
        $this->_controller = 'adminhtml_AbandonedCart';
        parent::_construct();

        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('save');

        if ($this->getRequest()->getParam('quote_is_active')) {
            $this->addButton(
                'send',
                [
                    'label' => __('Send Email'),
                    'class' => 'primary'
                ],
                1
            );
        }
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/smtp/abandonedcart');
    }
}
