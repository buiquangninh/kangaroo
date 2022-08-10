<?php


namespace Mirasvit\CustomerSegment\Controller\Adminhtml\Segment;


class Chooser extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget
{
    public function execute()
    {
        $request = $this->getRequest();

        switch ($request->getParam('field')) {
            case 'city_id':
                $block = $this->_view->getLayout()->createBlock(
                    \Mirasvit\CustomerSegment\Block\Adminhtml\Promo\Widget\Chooser\City::class,
                    'promo_widget_chooser_sku',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                );
                break;

            case 'district_id':
                $block = $this->_view->getLayout()->createBlock(
                    \Mirasvit\CustomerSegment\Block\Adminhtml\Promo\Widget\Chooser\District::class,
                    'promo_widget_chooser_sku',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                );
                break;

            case 'ward_id':
                $block = $this->_view->getLayout()->createBlock(
                    \Mirasvit\CustomerSegment\Block\Adminhtml\Promo\Widget\Chooser\Ward::class,
                    'promo_widget_chooser_sku',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                );
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}
