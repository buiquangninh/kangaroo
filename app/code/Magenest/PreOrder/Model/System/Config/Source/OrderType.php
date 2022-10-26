<?php
namespace Magenest\PreOrder\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OrderType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const VALUE_NONE = 0;
    const VALUE_PREORDER = 1;
    const VALUE_BACKORDER = 2;

    public static function _toOption()
    {
        return [
            ['value' => self::VALUE_NONE, 'label' => __("None")],
            ['value' => self::VALUE_PREORDER, 'label' => __("Pre-Order")],
            ['value' => self::VALUE_BACKORDER, 'label' => __("Backorder")]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        return $this->_toOption();
    }
}
