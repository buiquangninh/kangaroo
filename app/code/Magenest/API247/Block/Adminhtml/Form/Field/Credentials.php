<?php
namespace Magenest\API247\Block\Adminhtml\Form\Field;

use Magenest\API247\Model\Carrier\API247;

class Credentials extends \Magenest\ViettelPostCarrier\Block\Adminhtml\Form\Field\Credentials
{
    protected $carrier = API247::CODE;
}
