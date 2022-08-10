<?php

namespace Magenest\ViettelPostCarrier\Block\Adminhtml\Form\Field;

use Magenest\GiaoHangTietKiem\Block\Adminhtml\Form\Field\ApiToken;
use Magenest\ViettelPostCarrier\Model\Carrier\ViettelPost;

class Credentials extends ApiToken
{
    protected $carrier = ViettelPost::CODE;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('region', ['label' => __('Region'), 'class' => 'required-entry', 'attribute' => ['readonly']]);
        $this->addColumn('username', ['label' => __('Username')]);
        $this->addColumn('password', ['label' => __('Password'), 'type' => 'password']);
        $this->_addAfter = false;
    }
}
