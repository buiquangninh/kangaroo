<?php
namespace Magenest\PreOrder\Ui\DataProvider\Product\Form;

use Magenest\PreOrder\Model\System\Config\Source\OrderType;
use Magenest\PreOrder\Setup\Patch\Data\AddButtonLabelAttribute;
use Magenest\PreOrder\Setup\Patch\Data\AddOrderTypeAttribute;
use Magenest\PreOrder\Setup\Patch\Data\AddStockLabelAttribute;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

class Modifier extends AbstractModifier
{
    /** @var ArrayManager */
    protected $_arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(ArrayManager $arrayManager)
    {
        $this->_arrayManager = $arrayManager;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        $this->customizeOrderType($meta);
        $this->customizeButtonLabel($meta);
        $this->customizeStockLabel($meta);
        return $meta;
    }

    /**
     * @param $meta
     * @return void
     */
    private function customizeOrderType(&$meta)
    {
        // Remove old container
        $orderType = $this->_arrayManager->findPath(
            AddOrderTypeAttribute::ORDER_TYPE_ATTRIBUTE,
            $meta,
            null,
            'children'
        );
        $meta = $this->_arrayManager->remove($this->_arrayManager->slicePath($orderType, 0, -2), $meta);

        $orderTypeDefault = $this->_arrayManager->findPath(
            AddOrderTypeAttribute::ORDER_TYPE_DEFAULT_ATTRIBUTE,
            $meta,
            null,
            'children'
        );
        $meta = $this->_arrayManager->remove($this->_arrayManager->slicePath($orderTypeDefault, 0, -2), $meta);

        // Create new, unified container
        $orderTypeContainer = $this->_arrayManager->findPath(
            AddOrderTypeAttribute::ORDER_TYPE_CONTAINER,
            $meta,
            null,
            'children'
        );
        if ($orderTypeContainer) {
            $meta = $this->_arrayManager->merge(
                $orderTypeContainer . '/arguments/data',
                $meta,
                [
                    'template' => 'templates/container/default',
                    'type' => 'group',
                    'config' => [
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => __("Pre-order/Backorder"),
                        'validateWholeGroup' => false
                    ]
                ]
            );

            $meta = $this->_arrayManager->set(
                $orderTypeContainer . '/attributes',
                $meta,
                [
                    'class' => Container::class,
                    'component' => 'Magento_Ui/js/form/components/group',
                    'name' => AddOrderTypeAttribute::ORDER_TYPE_CONTAINER,
                ]
            );

            $useDefaultAttribute = AddOrderTypeAttribute::ORDER_TYPE_DEFAULT_ATTRIBUTE;
            $meta = $this->_arrayManager->set(
                $orderTypeContainer . '/children',
                $meta,
                [
                    AddOrderTypeAttribute::ORDER_TYPE_ATTRIBUTE => [
                        'attributes' => [
                            'class' => Field::class,
                            'name' => AddOrderTypeAttribute::ORDER_TYPE_ATTRIBUTE,
                            'formElement' => 'input'
                        ],
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Select::NAME,
                                    'label' => __(""),
                                    'dataType' => "text",
                                    'options' => OrderType::_toOption(),
                                    'imports' => [
                                        'disabled' => "inputName = product[$useDefaultAttribute]:checked"
                                    ]
                                ]
                            ]
                        ]
                    ],
                    AddOrderTypeAttribute::ORDER_TYPE_DEFAULT_ATTRIBUTE => [
                        'attributes' => [
                            'class' => Field::class,
                            'name' => AddOrderTypeAttribute::ORDER_TYPE_DEFAULT_ATTRIBUTE,
                            'formElement' => 'checkbox'
                        ],
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Checkbox::NAME,
                                    'prefer' => 'checkbox',
                                    'description' => __("Use Config Settings"),
                                    'valueMap' => [
                                        'false' => '0',
                                        'true'  => '1'
                                    ],
                                    'dataType' => "boolean",
                                    'default'  => 0
                                ]
                            ]
                        ]
                    ]
                ]
            );
        }
    }

    /**
     * @param $meta
     * @return void
     */
    private function customizeButtonLabel(&$meta)
    {
        // Remove old container
        $buttonLabel = $this->_arrayManager->findPath(
            AddButtonLabelAttribute::BUTTON_LABEL_ATTRIBUTE,
            $meta,
            null,
            'children'
        );
        $meta = $this->_arrayManager->remove($this->_arrayManager->slicePath($buttonLabel, 0, -2), $meta);

        $buttonLabelDefault = $this->_arrayManager->findPath(
            AddButtonLabelAttribute::BUTTON_LABEL_DEFAULT_ATTRIBUTE,
            $meta,
            null,
            'children'
        );
        $meta = $this->_arrayManager->remove($this->_arrayManager->slicePath($buttonLabelDefault, 0, -2), $meta);

        // Create new, unified container
        $buttonLabelContainer = $this->_arrayManager->findPath(
            AddButtonLabelAttribute::BUTTON_LABEL_CONTAINER,
            $meta,
            null,
            'children'
        );
        if ($buttonLabelContainer) {
            $meta = $this->_arrayManager->merge(
                $buttonLabelContainer . '/arguments/data',
                $meta,
                [
                    'template' => 'templates/container/default',
                    'type' => 'group',
                    'config' => [
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => __("Add to Cart Button Label"),
                        'validateWholeGroup' => false
                    ]
                ]
            );

            $meta = $this->_arrayManager->set(
                $buttonLabelContainer . '/attributes',
                $meta,
                [
                    'class' => Container::class,
                    'component' => 'Magento_Ui/js/form/components/group',
                    'name' => AddButtonLabelAttribute::BUTTON_LABEL_CONTAINER,
                ]
            );

            $useDefaultAttribute = AddButtonLabelAttribute::BUTTON_LABEL_DEFAULT_ATTRIBUTE;
            $meta = $this->_arrayManager->set(
                $buttonLabelContainer . '/children',
                $meta,
                [
                    AddButtonLabelAttribute::BUTTON_LABEL_ATTRIBUTE => [
                        'attributes' => [
                            'class' => Field::class,
                            'name' => AddButtonLabelAttribute::BUTTON_LABEL_ATTRIBUTE,
                            'formElement' => 'input'
                        ],
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Input::NAME,
                                    'label' => __(""),
                                    'dataType' => "text",
                                    'imports' => [
                                        'disabled' => "inputName = product[$useDefaultAttribute]:checked"
                                    ]
                                ]
                            ]
                        ]
                    ],
                    AddButtonLabelAttribute::BUTTON_LABEL_DEFAULT_ATTRIBUTE => [
                        'attributes' => [
                            'class' => Field::class,
                            'name' => AddButtonLabelAttribute::BUTTON_LABEL_DEFAULT_ATTRIBUTE,
                            'formElement' => 'checkbox'
                        ],
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Checkbox::NAME,
                                    'prefer' => 'checkbox',
                                    'description' => __("Use Config Settings"),
                                    'valueMap' => [
                                        'false' => '0',
                                        'true'  => '1'
                                    ],
                                    'dataType' => "boolean",
                                    'default'  => 0
                                ]
                            ]
                        ]
                    ]
                ]
            );
        }
    }

    /**
     * @param $meta
     * @return void
     */
    private function customizeStockLabel(&$meta)
    {
        // Remove old container
        $stockLabel = $this->_arrayManager->findPath(
            AddStockLabelAttribute::STOCK_LABEL_ATTRIBUTE,
            $meta,
            null,
            'children'
        );
        $meta = $this->_arrayManager->remove($this->_arrayManager->slicePath($stockLabel, 0, -2), $meta);

        $stockLabelDefault = $this->_arrayManager->findPath(
            AddStockLabelAttribute::STOCK_LABEL_DEFAULT_ATTRIBUTE,
            $meta,
            null,
            'children'
        );
        $meta = $this->_arrayManager->remove($this->_arrayManager->slicePath($stockLabelDefault, 0, -2), $meta);

        // Create new, unified container
        $stockLabelContainer = $this->_arrayManager->findPath(
            AddStockLabelAttribute::STOCK_LABEL_CONTAINER,
            $meta,
            null,
            'children'
        );
        if ($stockLabelContainer) {
            $meta = $this->_arrayManager->merge(
                $stockLabelContainer . '/arguments/data',
                $meta,
                [
                    'template' => 'templates/container/default',
                    'type' => 'group',
                    'config' => [
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => __("Stock Status Label"),
                        'validateWholeGroup' => false
                    ]
                ]
            );

            $meta = $this->_arrayManager->set(
                $stockLabelContainer . '/attributes',
                $meta,
                [
                    'class' => Container::class,
                    'component' => 'Magento_Ui/js/form/components/group',
                    'name' => AddStockLabelAttribute::STOCK_LABEL_CONTAINER,
                ]
            );

            $useDefaultAttribute = AddStockLabelAttribute::STOCK_LABEL_DEFAULT_ATTRIBUTE;
            $meta = $this->_arrayManager->set(
                $stockLabelContainer . '/children',
                $meta,
                [
                    AddStockLabelAttribute::STOCK_LABEL_ATTRIBUTE => [
                        'attributes' => [
                            'class' => Field::class,
                            'name' => AddStockLabelAttribute::STOCK_LABEL_ATTRIBUTE,
                            'formElement' => 'input'
                        ],
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Input::NAME,
                                    'label' => __(""),
                                    'dataType' => "text",
                                    'imports' => [
                                        'disabled' => "inputName = product[$useDefaultAttribute]:checked"
                                    ]
                                ]
                            ]
                        ]
                    ],
                    AddStockLabelAttribute::STOCK_LABEL_DEFAULT_ATTRIBUTE => [
                        'attributes' => [
                            'class' => Field::class,
                            'name' => AddStockLabelAttribute::STOCK_LABEL_DEFAULT_ATTRIBUTE,
                            'formElement' => 'checkbox'
                        ],
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Checkbox::NAME,
                                    'prefer' => 'checkbox',
                                    'description' => __("Use Config Settings"),
                                    'valueMap' => [
                                        'false' => '0',
                                        'true'  => '1'
                                    ],
                                    'dataType' => "boolean",
                                    'default'  => 0
                                ]
                            ]
                        ]
                    ]
                ]
            );
        }
    }
}
