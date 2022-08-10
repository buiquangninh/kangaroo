<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * User: leo
 * Date: 25/06/2022
 * Time: 15:38
 */
declare(strict_types=1);

namespace Lof\FlashSales\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Position extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $html = '<input class="input-text validate-digits validate-zero-or-greater validate-number" data-form-part="lof_flashsales_form" type="text" name="position[' . $item['entity_id'] . ']" value="'. $item[$fieldName] .'">';
                    $item[$fieldName] = $html;
                }
            }
        }

        return $dataSource;
    }
}
