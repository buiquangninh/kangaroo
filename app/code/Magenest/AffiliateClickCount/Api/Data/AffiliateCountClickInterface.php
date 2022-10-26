<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Api\Data;

interface AffiliateCountClickInterface
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CUSTOMER_ID = 'customer_id';
    const REFERAL_URL = 'referal_url';
    const AFFILIATE_CODE = 'affiliate_code';
    const AFFILIATECOUNTCLICK_ID = 'affiliatecountclick_id';

    /**
     * Get affiliatecountclick_id
     * @return string|null
     */
    public function getAffiliatecountclickId();

    /**
     * Set affiliatecountclick_id
     * @param string $affiliatecountclickId
     * @return \Magenest\AffiliateClickCount\AffiliateCountClick\Api\Data\AffiliateCountClickInterface
     */
    public function setAffiliatecountclickId($affiliatecountclickId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Magenest\AffiliateClickCount\AffiliateCountClick\Api\Data\AffiliateCountClickInterface
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
     * @return \Magenest\AffiliateClickCount\AffiliateCountClick\Api\Data\AffiliateCountClickInterface
     */
    public function setReferalUrl($referalUrl);

    /**
     * Get affiliate_code
     * @return string|null
     */
    public function getAffiliateCode();

    /**
     * Set affiliate_code
     * @param string $affiliateCode
     * @return \Magenest\AffiliateClickCount\AffiliateCountClick\Api\Data\AffiliateCountClickInterface
     */
    public function setAffiliateCode($affiliateCode);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Magenest\AffiliateClickCount\Affiliate\Api\Data\AffiliateCountClickInterface
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
     * @return \Magenest\AffiliateClickCount\Affiliate\Api\Data\AffiliateCountClickInterface
     */
    public function setUpdatedAt($updatedAt);
}
