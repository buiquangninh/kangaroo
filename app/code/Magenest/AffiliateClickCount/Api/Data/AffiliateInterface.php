<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Api\Data;

interface AffiliateInterface
{

    const CUSTOMER_ID = 'customer_id';
    const REFERAL_URL = 'referal_url';
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';
    const AFFILIATE_ID = 'affiliate_id';
    const AFFILIATE_CODE = 'affiliate_code';

    /**
     * Get affiliate_id
     * @return string|null
     */
    public function getAffiliateId();

    /**
     * Set affiliate_id
     * @param string $affiliateId
     * @return \Magenest\AffiliateClickCount\Affiliate\Api\Data\AffiliateInterface
     */
    public function setAffiliateId($affiliateId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Magenest\AffiliateClickCount\Affiliate\Api\Data\AffiliateInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get referal_url
     * @return string|null
     */
    public function getReferalUrl();

    /**
     * Set referal_url
     * @param string $referalUrl
     * @return \Magenest\AffiliateClickCount\Affiliate\Api\Data\AffiliateInterface
     */
    public function setReferalUrl($referalUrl);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Magenest\AffiliateClickCount\Affiliate\Api\Data\AffiliateInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Magenest\AffiliateClickCount\Affiliate\Api\Data\AffiliateInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get affiliate_code
     * @return string|null
     */
    public function getAffiliateCode();

    /**
     * Set affiliate_code
     * @param string $affiliateCode
     * @return \Magenest\AffiliateClickCount\Affiliate\Api\Data\AffiliateInterface
     */
    public function setAffiliateCode($affiliateCode);
}

