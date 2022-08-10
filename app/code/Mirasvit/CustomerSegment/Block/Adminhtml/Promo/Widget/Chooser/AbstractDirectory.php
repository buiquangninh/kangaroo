<?php

namespace Mirasvit\CustomerSegment\Block\Adminhtml\Promo\Widget\Chooser;

use Magento\Backend\Block\Widget\Grid\Column;

abstract class AbstractDirectory extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $prefix = '';

    protected $defaultField = '';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_cpCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_cpCollectionInstance;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId($this->prefix . 'ChooserGrid_' . $this->getId());
        }

        $form = $this->getJsFormObject();
        $this->setRowClickCallback("{$form}.chooserGridRowClick.bind({$form})");
        $this->setCheckboxCheckCallback("{$form}.chooserGridCheckboxCheck.bind({$form})");
        $this->setRowInitCallback("{$form}.chooserGridRowInit.bind({$form})");
        $this->setDefaultSort($this->defaultField);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_selected') {
            $selected = $this->_getSelectedItems();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter($this->defaultField, ['in' => $selected]);
            } else {
                $this->getCollection()->addFieldToFilter($this->defaultField, ['nin' => $selected]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare Catalog Product Collection for attribute SKU in Promo Conditions SKU chooser
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->_getCpCollectionInstance());

        return parent::_prepareCollection();
    }

    /**
     * Get catalog product resource collection instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getCpCollectionInstance()
    {
        if (!$this->_cpCollectionInstance) {
            $this->_cpCollectionInstance = $this->_cpCollection->create();
        }
        $this->joinDirectoryTables();
        return $this->_cpCollectionInstance;
    }

    /**
     * Define Chooser Grid Columns and filters
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_selected',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_selected',
                'values' => $this->_getSelectedItems(),
                'align' => 'center',
                'index' => $this->defaultField,
                'use_index' => true
            ]
        );

        $this->addColumn(
            $this->defaultField,
            ['header' => __('ID'), 'sortable' => true, 'width' => '60px', 'index' => $this->defaultField]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'width' => '60px',
                'index' => 'name',
            ]
        );
        $this->addColumn(
            'default_name',
            [
                'header' => __('Default Name'),
                'width' => '120px',
                'index' => 'default_name',
            ]
        );

        $this->setAdditionalColumn();

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/chooser',
            ['_current' => true, 'current_grid_id' => $this->getId(), 'collapse' => null]
        );
    }

    /**
     * @return mixed
     */
    protected function _getSelectedItems()
    {
        return $this->getRequest()->getPost('selected', []);
    }

    protected function setAdditionalColumn()
    {
        return;
    }

    protected function joinDirectoryTables()
    {
        return;
    }
}
