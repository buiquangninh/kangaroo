<?php


namespace Mirasvit\CustomerSegment\Block\Adminhtml\Promo\Widget\Chooser;


class District extends AbstractDirectory
{
    protected $prefix = 'district';

    protected $defaultField = 'district_id';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $eavAttSetCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $cpCollection
     * @param \Magento\Catalog\Model\Product\Type $catalogType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magenest\Directory\Model\ResourceModel\District\CollectionFactory $cpCollection,
        array $data = []
    ) {
        $this->_cpCollection = $cpCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function setAdditionalColumn()
    {
        $this->addColumn(
            'city_name',
            [
                'header' => __('City Name'),
                'width' => '120px',
                'index' => 'city_name',
            ]
        );
        return parent::setAdditionalColumn(); // TODO: Change the autogenerated stub
    }

    protected function joinDirectoryTables()
    {
        $resource = $this->_cpCollectionInstance->getResource();
        $this->_cpCollectionInstance->getSelect()->joinLeft(
            ["dce" =>$resource->getTable('directory_city_entity')],
            "main_table.city_id = dce.city_id",
            [
                "city_name" => "dce.default_name"
            ]
        );
        $this->_cpCollectionInstance->addFilterToMap('city_name', 'dce.default_name');
    }
}
