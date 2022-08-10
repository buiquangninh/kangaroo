<?php
namespace Magenest\PaymentEPay\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\DataType\Price;
/**
 * Class InstallmentModifier
 * @package Magenest\PaymentEPay\Ui\DataProvider\Product\Form\Modifier
 */
class InstallmentModifier extends AbstractModifier
{
    /** @var ArrayManager */
    protected $_arrayManager;

    /** @var LocatorInterface */
    private $_locator;

    private $_storeManager;

    /**
     * InstallmentModifier constructor.
     * @param ArrayManager $arrayManager
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ArrayManager $arrayManager,
        LocatorInterface $locator,
        StoreManagerInterface $storeManager
    ) {
        $this->_arrayManager = $arrayManager;
        $this->_locator = $locator;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->addDynamicRowsInstallmentOptions($meta);
        return $meta;
    }

    /**
     * @param $meta
     */
    private function addDynamicRowsInstallmentOptions(&$meta)
    {
        $path = $this->_arrayManager->findPath(
            'installment_options',
            $meta,
            null,
            'children'
        );
        if ($path) {
            $meta = $this->_arrayManager->merge(
                $path . self::META_CONFIG_PATH,
                $meta,
                [
                    'componentType' => DynamicRows::NAME,
                    'label' => __('Installment Options'),
                    'renderDefaultRecord' => false,
                    'recordTemplate' => 'record',
                    'required' => false,
                    'dataScope' => '',
                    'dndConfig' => [
                        'enabled' => false,
                    ],
                    'disabled' => false,
                ]
            );

            $meta = $this->_arrayManager->set(
                $path . '/children',
                $meta,
                ['record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => ''
                            ],
                        ],
                    ],
                    'children' => [
                        'rate' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Price::NAME,
                                        'label' => __('Option'),
                                        'options' => [
                                            'minDate' => '0'
                                        ],
                                        'validation' => [
                                            'validate-zero-or-greater' => true,
                                            'required-entry' => true
                                        ],
                                        'addbefore' => $this->_storeManager->getStore()->getBaseCurrency()->getCurrencySymbol(),
                                    ],
                                ],
                            ],
                        ]
                    ],
                ]]
            );

            $meta = $this->_arrayManager->set(
                $path . '/children/record/children/actionDelete' . self::META_CONFIG_PATH,
                $meta,
                [
                    'componentType' => 'actionDelete',
                    'dataType' => Text::NAME,
                    'label' => ''
                ]
            );
        }
    }
}
