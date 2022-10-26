<?php
namespace Magenest\CareSoft\Model\Config\Source;

use Magenest\CareSoft\Model\CaresoftApi;
use Magento\Framework\Data\OptionSourceInterface;

class CustomField implements OptionSourceInterface
{
    /** @var CaresoftApi */
    private $caresoftApi;

    /**
     * @param CaresoftApi $caresoftApi
     */
    public function __construct(CaresoftApi $caresoftApi)
    {
        $this->caresoftApi = $caresoftApi;
    }

    /**
	 * @inheritDoc
	 */
	public function toOptionArray()
    {
        $result = [['label' => __('-- Please Select --'), 'value' => '']];

        $fields = $this->caresoftApi->getCustomFields();
        foreach ($fields as $field) {
            if (isset($field['custom_field_id']) && isset($field['custom_field_lable'])) {
                $result[] = ['value' => $field['custom_field_id'], 'label' => $field['custom_field_lable']];
            }
        }

        return $result;
	}
}
