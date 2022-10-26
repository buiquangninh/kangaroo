<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\Customer\Controller\Account;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
{
    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        return $this->_forward('edit');
    }
}
