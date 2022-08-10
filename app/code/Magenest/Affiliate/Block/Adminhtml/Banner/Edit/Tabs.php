<?php


namespace Magenest\Affiliate\Block\Adminhtml\Banner\Edit;

/**
 * Class Tabs
 * @package Magenest\Affiliate\Block\Adminhtml\Banner\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('banner_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Banner Information'));
    }
}
