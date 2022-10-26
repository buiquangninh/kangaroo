<?php

namespace Magenest\Affiliate\Block;

use Magenest\Affiliate\Helper\Data;
use Magenest\Affiliate\Helper\Data as DataHelper;
use Magenest\Affiliate\Model\Account\Status;
use Magenest\AffiliateCatalogRule\Helper\Constant;
use Magenest\Customer\Block\AbstractNavigation;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Html\Links;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Navigation
 * @package Magenest\Affiliate\Block
 */
class AffiliateNavigation extends AbstractNavigation
{
    const ALLOW_BOTH = 'both';
    const ALLOW_GUEST = 'guess';
    const ALLOW_LOGIN = 'login';

    /**
     * Search redundant /index and / in url
     */
    const REGEX_URL_PATTERN = '/affiliate/';

    /** @var HttpContext */
    protected $httpContext;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    protected $excludeAffiliateMenuItem = ['refer', 'wallet', 'setting'];

    public function __construct(
        Context $context,
        Data $helper,
        DataHelper $dataHelper,
        HttpContext $httpContext,
        array $data = []
    ) {
        $this->dataHelper  = $dataHelper;
        $this->httpContext = $httpContext;
        parent::__construct($context, $helper, $data);
    }

    /**
     * @inheritDoc
     */
    protected function additionalClass()
    {
        return 'affiliate';
    }

    /**
     * @inheritDoc
     */
    public function getLinks()
    {
        $affiliateContext = $this->httpContext->getValue(Constant::IS_AFFILIATE_CONTEXT);
        $links            = parent::getLinks();
        $affiliateAccount = $this->dataHelper->getCurrentAffiliate();
        foreach ($links as $index => $link) {
            if ($affiliateContext && $affiliateAccount->getData('is_limited') && in_array($link->getCode(), ['welcome','groups', 'collaborator'])) {
                unset($links[$index]);
            }

            if ($link->getActive() === self::ALLOW_GUEST && $affiliateContext) {
                unset($links[$index]);
            }

            if ($link->getActive() === self::ALLOW_LOGIN && !in_array($link->getCode(), $this->excludeAffiliateMenuItem) && !$affiliateContext) {
                unset($links[$index]);
            }

            if ($link->getCode() == "signup" && $affiliateAccount->getStatus() == Status::NEED_APPROVED) {
                unset($links[$index]);
            }
        }
        return $links;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = Links::_toHtml();

        $result = '<li class="nav item ' . $this->additionalClass();
        if ($this->_request->getModuleName() == "affiliate") {
            $result = '<li class="nav item return-previous"><a class="return" href="' . $this->getUrl('customer/account') . '" >' . __('Back to profile') . '</a></li>' . $result;
        }
        if ($this->isCurrent()) {
            $result .= ' current';
        }
        if (empty($html) || $this->_request->getModuleName() != "affiliate") {
            $result .= '"><a href="' . $this->getUrl($this->getPath()) . '" >' . __($this->getLabel()) . '</a>';
            $html   = "";
        } else {
            $result .= '"><a>' . __($this->getLabel()) . '</a>';
        }
        $result .= $html;
        return $result;
    }
}
