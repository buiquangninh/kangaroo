<?php
namespace Magenest\AdditionalRegisterForm\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magenest\Directory\Model\ResourceModel\City\CollectionFactory;
class City extends AbstractSource{

    private $cityCollectionFactory;

    public function __construct(CollectionFactory $cityCollectionFactory)
    {
        $this->cityCollectionFactory = $cityCollectionFactory;
    }
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->cityCollectionFactory->create()->load()->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __('Please select a city.')]);
        }
        return $this->_options;
    }
}
