<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Model;

use Magenest\AffiliateClickCount\Api\Data\AffiliateInterface;
use Magento\Framework\Model\AbstractModel;

class Affiliate extends AbstractModel implements AffiliateInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magenest\AffiliateClickCount\Model\ResourceModel\Affiliate::class);
    }

    /**
     * @inheritDoc
     */
    public function getAffiliateId()
    {
        return $this->getData(self::AFFILIATE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAffiliateId($affiliateId)
    {
        return $this->setData(self::AFFILIATE_ID, $affiliateId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getReferalUrl()
    {
        return $this->getData(self::REFERAL_URL);
    }

    /**
     * @inheritDoc
     */
    public function setReferalUrl($referalUrl)
    {
        return $this->setData(self::REFERAL_URL, $referalUrl);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }


    /**
     * @inheritDoc
     */
    public function getAffiliateCode()
    {
        return $this->getData(self::AFFILIATE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setAffiliateCode($affiliateCode)
    {
        return $this->setData(self::AFFILIATE_CODE, $affiliateCode);
    }
}

