<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Smtp
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Smtp\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class DatePicker
 * @package Magetop\Smtp\Block\Adminhtml\System\Config
 */
class DatePicker extends Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'system/config/date-picker.phtml';

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);

        return $this->_decorateRowHtml($element, $this->toHtml());
    }

    /**
     * @param AbstractElement $element
     *
     * @return mixed
     */
    public function getFieldName(AbstractElement $element)
    {
        return $element->getName();
    }
}
