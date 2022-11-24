<?php

namespace Magenest\MomoPay\Model;

class ClientRequestBuilder
{
    /**
     * @var \Magenest\MomoPay\Helper\Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $clientFactory;

    /**
     * @var \Magento\Framework\HTTP\ZendClient
     */
    protected $_clientRequest;

    /**
     * ClientRequestBuilder constructor.
     *
     * @param \Magenest\MomoPay\Helper\Helper $helper
     * @param \Magento\Framework\HTTP\ZendClientFactory $clientFactory
     */
    public function __construct(
        \Magenest\MomoPay\Helper\Helper $helper,
        \Magento\Framework\HTTP\ZendClientFactory $clientFactory
    ) {
        $this->helper = $helper;
        $this->clientFactory = $clientFactory;
    }

    /**
     * @return \Magento\Framework\HTTP\ZendClient
     * @throws \Zend_Http_Client_Exception
     */
    public function initClient()
    {
        if ($this->_clientRequest !== null) {
            $this->_clientRequest = null;
        }
        $this->_clientRequest = $this->clientFactory->create();
        $this->setClientHeader();

        return $this->_clientRequest;
    }

    /**
     * @throws \Zend_Http_Client_Exception
     */
    private function setClientHeader()
    {
        if ($this->_clientRequest == null) {
            return;
        }
        $this->_clientRequest->setHeaders('Content-Type', 'application/json');
    }

    /**
     * @param $method
     * @param $uri
     * @param $payload
     * @param null $responseInterface
     * @return array|bool|float|int|mixed|string|null
     * @throws \Zend_Http_Client_Exception
     */
    public function makeClientRequest($method, $uri, $payload, $responseInterface = null)
    {
        $client = $this->_clientRequest;
        $client->setUri($uri);
        $client->setMethod($method);
        if (is_array($payload)) {
            $payload = $this->helper->serialize($payload);
        }
        $client->setRawData($payload);
        $client->setUrlEncodeBody(true);
        $result = $client->request();

        return $this->prepareResponse($result, $responseInterface);
    }

    /**
     * @param $client
     * @param $request
     */
    public function populateWithRequest($client, $request)
    {
        $client->setConfig($request->getClientConfig());
        $client->setMethod($request->getMethod());
        $headers = $request->getHeaders();
        if ($headers) {
            $client->setHeaders($headers);
        }
        $client->setUrlEncodeBody($request->shouldEncode());
    }

    /**
     * @param \Zend_Http_Response $result
     * @param null $responseInterface
     * @return array|bool|float|int|mixed|string|null
     */
    private function prepareResponse(\Zend_Http_Response $result, $responseInterface = null)
    {
        $responseBody = $result->getBody();
        if (!is_array($responseBody)) {
            $responseBody = $this->helper->unserialize($responseBody);
        }
        $this->helper->debug(__('Client Response: %1', print_r($responseBody, true)));
        if (isset($responseInterface)) {
            return $this->helper->createObject($responseBody, $responseInterface);
        }

        return $responseBody;
    }
}
