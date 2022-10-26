<?php
namespace Magenest\PaymentEPay\Block\Adminhtml\Form\Field;

use Magenest\PaymentEPay\Block\Adminhtml\Form\Field\TypeRenderer;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class AdditionalEmail
 */
class DynamicInstallmentOptions extends AbstractFieldArray
{
    protected $typeRenderer;

    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn('value_type', [
            'label' => __('Type'),
            'renderer' => $this->getTypeRenderer()
        ]);

        $this->addColumn('rate', ['label' => __('Options'), 'class' => 'required-entry  admin__control-text']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function getTypeRenderer()
    {
        if (!$this->typeRenderer) {
            $this->typeRenderer = $this->getLayout()->createBlock(
                TypeRenderer::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->typeRenderer;
    }
}
