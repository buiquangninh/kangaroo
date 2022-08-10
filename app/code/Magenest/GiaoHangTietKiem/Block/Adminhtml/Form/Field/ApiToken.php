<?php
namespace Magenest\GiaoHangTietKiem\Block\Adminhtml\Form\Field;

use Magenest\GiaoHangTietKiem\Model\Carrier\GiaoHangTietKiem;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\AbstractBlock;

class ApiToken extends AbstractFieldArray
{
    protected $carrier = GiaoHangTietKiem::CODE;

    /**
     * @var string
     */
    protected $_template = 'Magenest_GiaoHangTietKiem::system/config/form/field/array.phtml';

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('region', ['label' => __('Region'), 'class' => 'required-entry', 'attribute' => ['readonly']]);
        $this->addColumn('api_token', ['label' => __('Token')]);
        $this->_addAfter = false;
    }

    /**
     * Add a column to array-grid
     *
     * @param string $name
     * @param array $params
     * @return void
     */
    public function addColumn($name, $params)
    {
        $this->_columns[$name] = [
            'type'      => $this->_getParam($params, 'type'),
            'label'     => $this->_getParam($params, 'label', 'Column'),
            'size'      => $this->_getParam($params, 'size', false),
            'style'     => $this->_getParam($params, 'style'),
            'class'     => $this->_getParam($params, 'class'),
            'attribute' => $this->_getParam($params, 'attribute'),
            'renderer'  => false
        ];
        if (!empty($params['renderer']) && $params['renderer'] instanceof AbstractBlock) {
            $this->_columns[$name]['renderer'] = $params['renderer'];
        }
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of \Magento\Framework\DataObject
     *
     * @return array
     */
    public function getArrayRows()
    {
        $result = [];

        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $this->getElement();
        $values  = $element->getValue() && is_array($element->getValue()) ? $element->getValue() : [];

        foreach (['mien_bac' => __("North"), 'mien_trung' => __("Central"), 'mien_nam' => __("South")] as $rowId => $label) {
            $row              = [];
            $row['_id']       = $rowId . '_' . $this->carrier;
            $row['region']    = $label;
            $row['username']  = '';
            $row['password']  = '';
            $row['api_token'] = '';
            $rowColumnValues  = [];
            if (isset($values[$rowId . '_' . $this->carrier])) {
                unset($values[$rowId . '_' . $this->carrier]['region']);
                foreach ($values[$rowId . '_' . $this->carrier] as $key => $value) {
                    $row[$key] = $value;
                    $values[$rowId . '_' . $this->carrier][$key] = $value;
                    $rowColumnValues[$this->_getCellInputElementId($rowId . $this->carrier, $key)]
                        = $values[$rowId . '_' . $this->carrier][$key];
                }
            }
            $row['column_values']            = $rowColumnValues;
            $result[$rowId . '_' . $this->carrier] = new DataObject($row);
            $this->_prepareArrayRow($result[$rowId . '_' . $this->carrier]);
        }

        return $result;
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new \Exception('Wrong column name specified.');
        }
        $column    = $this->_columns[$columnName];
        $inputName = $this->_getCellInputElementName($columnName);

        if ($column['renderer']) {
            return $column['renderer']->setInputName(
                $inputName
            )->setInputId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setColumnName(
                $columnName
            )->setColumn(
                $column
            )->toHtml();
        }

        return '<input type="' . ($column['type'] ?? 'text') . '"' .
            ' id="' . $this->_getCellInputElementId('<%- _id %>', $columnName) . '"' .
            ' name="' . $inputName . '"' .
            ' value="<%- ' . $columnName . ' %>" ' .
            ($column['size'] ? 'size="' . $column['size'] . '"' : '') .
            ' class="' . ($column['class'] ?? 'input-text') . '"' .
            (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') .
            (isset($column['attribute']) ? ' ' . implode(' ', $column['attribute']) . ' ' : '') . '/>';
    }
}
