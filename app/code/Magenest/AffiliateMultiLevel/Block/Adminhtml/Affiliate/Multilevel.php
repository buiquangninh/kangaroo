<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\AffiliateMultiLevel\Block\Adminhtml\Affiliate;

/**
 * Backend customers by orders report content block
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Multilevel extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Define children block group
     *
     * @var string
     */
    protected $_blockGroup = 'Magento_Reports';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_Affiliate';
        $this->_controller = 'adminhtml_report_index';
        $this->_headerText = __('Affiliate multi level');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
