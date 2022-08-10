<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\CustomAdvancedReports\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\User\Model\User;

/**
 * Class Store
 */
class assignedTo extends Column
{
    /**
     * @var User
     */
    protected User $userFactory;

   public function __construct(
       ContextInterface $context,
       UiComponentFactory $uiComponentFactory,
       \Magento\User\Model\User $userFactory,
       array $components = [],
       array $data = []
   ) {
       $this->userFactory = $userFactory;
       parent::__construct($context, $uiComponentFactory, $components, $data);
   }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->getUserName($item[$this->getData('name')]);
            }
        }

        return $dataSource;
    }

    private function getUserName($userId) {
        $user = $this->userFactory->load($userId);
        return $user->getName();
    }
}
