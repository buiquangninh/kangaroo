<?php
/**
 * Copyright © Affiliate All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\Affiliate\Controller\Adminhtml\Group;

use Magenest\Affiliate\Model\GroupFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class NewAction extends \Magenest\Affiliate\Controller\Adminhtml\Group
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Context $context
     * @param GroupFactory $groupFactory
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        GroupFactory $groupFactory,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context, $groupFactory, $coreRegistry, $resultPageFactory);
    }

    /**
     * New action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}

