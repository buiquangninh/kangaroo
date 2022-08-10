<?php
/**
 * Created by PhpStorm.
 * User: doanhcn2
 * Date: 28/03/2019
 * Time: 10:51
 */

namespace Magenest\Slider\Ui\Component\Listing\Column;


use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class SliderType extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [], array $data = [])
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource); // TODO: Change the autogenerated stub
        if (isset($dataSource['data']['items'])) {
            foreach($dataSource['data']['items'] as & $item) {
                if (isset($item['type'])) {
                    switch ($item['type']) {
                        case 0:
                            $item['type'] = __("Banner");
                            break;
                        case 1:
                            $item['type'] = __("Slider");
                            break;
                        case 2:
                            $item['type'] = __("Slider Syncing");
                            break;
                    }
                }
            }
        }
        return $dataSource;
    }
}