<?php


namespace Mirasvit\CustomerSegment\Block\Adminhtml\Promo\Widget\Chooser;


class City extends AbstractDirectory
{
    protected $prefix = 'city';

    protected $defaultField = 'city_id';

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
        \Magenest\Directory\Model\ResourceModel\City\CollectionFactory $cpCollection,
        array $data = []
    ) {
        $this->_cpCollection = $cpCollection;
        parent::__construct($context, $backendHelper, $data);
    }
}
