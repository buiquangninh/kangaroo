<?php

namespace Magenest\PaymentEPay\Block\Adminhtml\Form\Field;

use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class TypeRenderer extends Select
{
    protected $priceOptions;

    public function __construct(ProductPriceOptionsInterface $priceOptions, Context $context, array $data = [])
    {
        $this->priceOptions = $priceOptions;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getTypeOptions());
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     */
    private function getTypeOptions(): array
    {
        return $this->priceOptions->toOptionArray();
    }
}
