<?php

namespace Magenest\MomoPay\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\TransferInterface;

abstract class AbstractClient implements \Magento\Payment\Gateway\Http\ClientInterface
{
    /**
     * @var \Magenest\MomoPay\Model\ClientRequestBuilder
     */
    protected $requestBuilder;
    /**
     * @var \Magenest\MomoPay\Helper\MomoHelper
     */
    protected $helper;

    public function __construct(
        \Magenest\MomoPay\Model\ClientRequestBuilder $requestBuilder,
        \Magenest\MomoPay\Helper\MomoHelper $helper
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->helper = $helper;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        try {
            $response = $this->process($transferObject);
        } catch (\Exception $e) {
            $this->helper->debug(__("Exception when calling api: \n%1", $e->getMessage()));
            $response['error'] = $e->getCode();
            $response['error_message'] = $e->getMessage();
        }

        return $response;
    }

    abstract protected function process($requestData);
}