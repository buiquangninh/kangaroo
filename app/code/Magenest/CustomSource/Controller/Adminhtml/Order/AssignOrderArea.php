<?php

namespace Magenest\CustomSource\Controller\Adminhtml\Order;

use Magenest\CustomSource\Model\Source\Area\Options;
use Magenest\CustomSource\Plugin\SetAreaCodeContext;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Psr\Log\LoggerInterface;

class AssignOrderArea extends Action
{
    const COOKIE_NAME_CODE = 'area_code';

    const COOKIE_NAME_LABEL = 'area_label';

    const COOKIE_DURATION = 86400; // lifetime in seconds

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Options
     */
    protected $areaOptions;

    /** @var HttpContext */
    protected $httpContext;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * AssignOrderArea constructor.
     * @param LoggerInterface $logger
     * @param Options $areaOptions
     * @param HttpContext $httpContext
     * @param Context $context
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        LoggerInterface         $logger,
        Options                 $areaOptions,
        HttpContext             $httpContext,
        Context $context,
        CookieManagerInterface  $cookieManager,
        CookieMetadataFactory   $cookieMetadataFactory
    ) {
        $this->logger = $logger;
        $this->areaOptions = $areaOptions;
        $this->httpContext = $httpContext;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $data = ['success' => false];
        try {
            $areaCode = $this->getRequest()->getPost('area_code');
            if ($areaCode && $this->checkValidAreaCode($areaCode)) {
                $data['success'] = true;
                $this->httpContext->setValue(
                    SetAreaCodeContext::AREA_CODE_CONTEXT,
                    $areaCode,
                    false
                );
            } else {
                $this->messageManager->addWarningMessage(__('Area does not exists'));
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        $result->setData($data);
        return $result;
    }

    /**
     * @param $areaCode
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    private function checkValidAreaCode($areaCode)
    {
        foreach ($this->areaOptions->toOptionArray() as $item) {
            if ($item['value'] == $areaCode) {
                $this->setPublicCookieArea(self::COOKIE_NAME_CODE, $item['value']);
                $this->setPublicCookieArea(self::COOKIE_NAME_LABEL, $item['label']);
                return true;
            }
        }
        return false;
    }

    /**
     * @param $cookieName
     * @param $value
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    private function setPublicCookieArea($cookieName, $value)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(self::COOKIE_DURATION)
            ->setPath('/')
            ->setHttpOnly(false); // JS-accessible

        $this->cookieManager->deleteCookie($cookieName, $metadata);
        $this->cookieManager->setPublicCookie(
            $cookieName,
            $value,
            $metadata
        );
    }
}
