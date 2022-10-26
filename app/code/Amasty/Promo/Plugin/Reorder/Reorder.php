<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Free Gift for Magento 2
*/

namespace Amasty\Promo\Plugin\Reorder;

class Reorder
{
    /**
     * @param \Magento\Sales\Controller\AbstractController\Reorder $subject
     */
    public function beforeExecute(\Magento\Sales\Controller\AbstractController\Reorder $subject)
    {
        \Amasty\Promo\Model\Storage::$isReorder = true;
    }
}
