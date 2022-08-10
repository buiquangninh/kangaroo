<?php
/**
 * FilterArgument
 *
 * @copyright Copyright © 2021 Landofcoder. All rights reserved.
 * @author    landofcoder@gmail.com
 */

namespace Lof\FlashSalesGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\FieldEntityAttributesInterface;

/**
 * Class FilterArgument
 * @package Lof\FlashSalesGraphQl\Model\Resolver
 */
class FilterArgument implements FieldEntityAttributesInterface
{

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * FilterArgument constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getEntityAttributes(): array
    {
        $fields = [];
        /** @var Field $field */
        foreach ($this->config->getConfigElement('FlashSale')->getFields() as $field) {
            $fields[$field->getName()] = [
                'type'      => 'String',
                'fieldName' => $field->getName(),
            ];
        }

        return $fields;
    }
}
