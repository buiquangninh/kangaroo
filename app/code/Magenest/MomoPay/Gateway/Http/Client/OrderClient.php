<?php

namespace Magenest\MomoPay\Gateway\Http\Client;

class OrderClient extends AbstractClient
{
    /**
     * @param $requestData
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    protected function process($requestData)
    {
        $data = $requestData->getBody();
        $uri = $requestData->getUri();

        $client = $this->requestBuilder->initClient();
        $this->requestBuilder->populateWithRequest($client, $requestData);
        $this->helper->debug(__('Make Order Request: %1', $uri));
        $this->helper->debug(__('Data Order Request: %1', print_r($data, true)));
        $result = $this->requestBuilder->makeClientRequest($requestData->getMethod(), $uri, $data, \Magenest\MomoPay\Api\Response\CreatePaymentResponseInterface::class);

        // $result =
        //     [
        //         'partnerCode' => 'MOMOUVEN20220607',
        //         'orderId' => '2000001809',
        //         'requestId' => '2000001809-1663827198',
        //         'amount' => '5354150',
        //         'responseTime' => '1663827198327',
        //         'message' => 'Successful.',
        //         'resultCode' => '0',
        //         'payUrl' => 'https://test-payment.momo.vn/v2/gateway/pay?t=TU9NT1VWRU4yMDIyMDYwN3wyMDAwMDAxODA5',
        //     ];
        return ['response' => $result];
    }
}