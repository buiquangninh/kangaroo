<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FlashSales
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\FlashSales\Model\Adminhtml\System\Config\Source;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

class Page implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * allows mode
     */
    const ALLOW_LOGIN = 1;

    const ALLOW_REGISTER = 2;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $pageOptions = $this->collectionFactory->create()->toOptionIdArray();
            $this->options = array_merge($this->addOptions(), $pageOptions);
        }
        return $this->options;
    }

    /**
     * @return array[]
     */
    public function addOptions()
    {
        return [
            [
                'value' => self::ALLOW_LOGIN,
                'label' => __('Login Page')
            ],
            [
                'value' => self::ALLOW_REGISTER,
                'label' => __('Registration Page')
            ]
        ];
    }
}
