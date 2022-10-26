<?php
namespace Magenest\Affiliate\Model\Message;

use Magenest\Affiliate\Model\ResourceModel\Account\CollectionFactory;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

class PendingRequest implements MessageInterface
{
    const IDENTITY = "affiliate_request";

    /** @var CollectionFactory */
    private $affiliateCollection;

    /** @var int */
    private $pendingCount = null;

    /** @var UrlInterface */
    private $urlBuilder;

    /**
     * @param CollectionFactory $affiliateCollection
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        CollectionFactory $affiliateCollection,
        UrlInterface $urlBuilder
    ) {
        $this->affiliateCollection = $affiliateCollection;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return int
     */
    public function getPendingCount()
    {
        if ($this->pendingCount === null) {
            $this->pendingCount = $this->affiliateCollection->create()
                ->addFieldToFilter('request_official', 1)
                ->getSize();
        }

        return $this->pendingCount;
    }

    /**
     * @inheritDoc
     */
    public function getIdentity()
    {
        return self::IDENTITY;
    }

    /**
     * @inheritDoc
     */
    public function isDisplayed()
    {
        return $this->getPendingCount() > 0;
    }

    /**
     * @inheritDoc
     */
    public function getText()
    {
        $messageDetails = __("There's currently %1 affiliate request(s). ", $this->getPendingCount());
        $messageDetails .= '<a href="' . $this->urlBuilder->getUrl('affiliate/account/index') . '">'
            . __('Review Now.') . '</a>';

        return $messageDetails;
    }

    /**
     * @inheritDoc
     */
    public function getSeverity()
    {
        return MessageInterface::SEVERITY_NOTICE;
    }
}
