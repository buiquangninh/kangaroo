<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-customer-segment
 * @version   1.2.1
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CustomerSegment\Model;


use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is export enabled.
     *
     * @return bool
     */
    public function isExportAllowed()
    {
        return (bool) $this->scopeConfig->getValue('customersegment/export/status');
    }
}