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

namespace Lof\FlashSales\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class DateResolver
{

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * Core store manager interface
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param TimezoneInterface $localeDate
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        TimezoneInterface $localeDate,
        StoreManagerInterface $storeManager
    ) {
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve configuration timezone
     *
     * @return string
     */
    public function getConfigTimezone()
    {
        return $this->localeDate->getConfigTimezone();
    }

    /**
     * Retrieve store timezone from configuration
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getConfigStoreTimezone()
    {
        return $this->localeDate->getConfigTimezone(
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore($this->storeManager->getStore()->getId())->getCode()
        );
    }

    /**
     * Convert date to UTC according to store timezone if $toUtc is true
     * or to store timezone if $toUtc is false
     *
     * @param string $date
     * @param bool $toUtc
     * @param null|string $format
     *
     * @return string
     * @throws \Exception
     */
    public function convertDate($date, $toUtc = true, $format = null)
    {
        if ($toUtc) {
            $dateObj = new \DateTime($date, new \DateTimeZone($this->getConfigTimezone()));
            $dateObj->setTimezone(new \DateTimeZone('UTC'));
        } else {
            $dateObj = new \DateTime($date, new \DateTimeZone('UTC'));
            $dateObj->setTimezone(new \DateTimeZone($this->getConfigTimezone()));
        }

        if ($format) {
            return  $dateObj->format($format);
        }

        return  $dateObj->format('Y-m-d H:i:s');
    }
}
