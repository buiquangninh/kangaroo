<?php /** @noinspection PhpUnused */

namespace Magenest\OutOfStockAtLast\Plugin\Model\Adapter;

use Magenest\CustomSource\Model\Source\Area\Options;

/**
 * Class AdditionalFieldMapperPlugin for es attributes mapping
 */
class AdditionalFieldMapperPlugin
{
    /**
     * @var Options
     */
    protected $areaOptions;

    /**
     * AdditionalFieldMapperPlugin constructor.
     * @param Options $areaOptions
     */
    public function __construct(
        Options $areaOptions
    ) {
        $this->areaOptions = $areaOptions;
        $this->prepareAllowFields();
    }

    /**
     * @var string[]
     */
    protected $allowedFields = [];

    /**
     * Missing mapped attribute code
     *
     * @param mixed $subject
     * @param array $result
     * @return array
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterGetAllAttributesTypes($subject, array $result): array
    {
        foreach ($this->allowedFields as $fieldName => $fieldType) {
            $result[$fieldName] = ['type' => $fieldType];
        }

        return $result;
    }

    /**
     * 3rd module Compatibility
     *
     * @param mixed $subject
     * @param array $result
     * @return array
     * @noinspection PhpUnused
     */
    public function afterBuildEntityFields($subject, array $result): array
    {
        return $this->afterGetAllAttributesTypes($subject, $result);
    }

    protected function prepareAllowFields()
    {
        foreach ($this->areaOptions->toOptionArray() as $option) {
            $this->allowedFields['out_of_stock_es_at_last_' . $option['value']] = 'integer';
        }
    }
}
