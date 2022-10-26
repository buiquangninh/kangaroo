<?php
namespace Magenest\AdditionalRegisterForm\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magenest\Directory\Model\ResourceModel\Ward\CollectionFactory;
class Ward extends AbstractSource{

    private $wardCollectionFactory;

    public function __construct(CollectionFactory $wardCollectionFactory)
    {
        $this->wardCollectionFactory = $wardCollectionFactory;
    }
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->wardCollectionFactory->create()->load()->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __('Please select a ward.')]);
        }
        return $this->_options;
    }
}
