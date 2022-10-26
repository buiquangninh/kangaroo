<?php
namespace Magenest\AdditionalRegisterForm\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magenest\Directory\Model\ResourceModel\District\CollectionFactory;
class District extends AbstractSource{

    private $districtCollectionFactory;

    public function __construct(CollectionFactory $districtCollectionFactory)
    {
        $this->districtCollectionFactory = $districtCollectionFactory;
    }
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->districtCollectionFactory->create()->load()->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __('Please select a district.')]);
        }
        return $this->_options;
    }
}
