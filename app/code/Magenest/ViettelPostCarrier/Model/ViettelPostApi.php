<?php
namespace Magenest\ViettelPostCarrier\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Response;

class ViettelPostApi
{
    const CREDENTIALS_CONFIG = "carriers/viettelPostCarrier/credentials";

    const LOGIN_ENDPOINT = "https://partner.viettelpost.vn/v2/user/Login";
    const CONNECT_ENDPOINT = "https://partner.viettelpost.vn/v2/user/ownerconnect";

    const ALL_PROVINCE_ENDPOINT = "https://partner.viettelpost.vn/v2/categories/listProvinceById?provinceId=-1";
    const ALL_DISTRICT_ENDPOINT = "https://partner.viettelpost.vn/v2/categories/listDistrict?provinceId=-1";
    const ALL_WARD_ENDPOINT = "https://partner.viettelpost.vn/v2/categories/listWards?districtId=-1";

    const PRICING_ENDPOINT = "https://partner.viettelpost.vn/v2/order/getPriceAll";
    const CREATE_ORDER_ENDPOINT = "https://partner.viettelpost.vn/v2/order/createOrder";

    /** @var bool */
    private $connected = false;

    /** @var AsyncClientInterface */
    private $asyncClient;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var SerializerInterface */
    private $serializer;

    /** @var string|null */
    private $token = null;

    /**
     * @param AsyncClientInterface $asyncClient
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     *
     * @throws \Throwable
     */
    public function __construct(
        AsyncClientInterface $asyncClient,
        ScopeConfigInterface $scopeConfig,
        SerializerInterface  $serializer
    ) {
        $this->asyncClient = $asyncClient;
        $this->scopeConfig = $scopeConfig;
        $this->serializer  = $serializer;
    }

    /**
     * Response parser for AsyncClientInterface (synchronously)
     *
     * Please make sure returned response is JSON (Content-Type = 'application/json') before using
     *
     * @param Request $request
     * @return array|bool|float|int|string|null
     *
     * @throws \Throwable
     */
    private function synchronousRequest(Request $request)
    {
        $response = $this->asyncClient->request($request)->get();
        try {
            $result = $this->serializer->unserialize($response->getBody());
        } catch (\Exception $exception) {
            $result = $this->serializer->serialize($response->getBody());
            throw new LocalizedException(__("ViettelPost: " . empty($result) ? $result : "Không có phản hồi"));
        }
        if ($response->getStatusCode() !== Response::HTTP_OK || $result['status'] !== Response::HTTP_OK) {
            $message = $result['message'] ?? "Can't send request to Viettel Post API. Please try again.";
            throw new LocalizedException(__("ViettelPost: " . $message));
        }

        return $result;
    }

    /**
     * CONNECT: POST@partner.viettelpost.vn/v2/user/ownerconnect
     *
     * @param string $area
     * @return $this
     * @throws \Throwable
     */
    public function connect($area = null)
    {
        if (!$this->connected) {
            $username = $password = null;
            $credentials = $this->serializer->unserialize($this->scopeConfig->getValue(self::CREDENTIALS_CONFIG));
            foreach ($credentials as $code => $credential) {
                if ($credential['username'] && $credential['password'] && ($code == $area || empty($area))) {
                    $username = $credential['username'];
                    $password = $credential['password'];
                    break;
                }
            }

            if (empty($username) || empty($password)) {
                throw new LocalizedException(__("No login credential for this region found."));
            }

            if (empty($this->token)) {
                $this->token = $this->login($username, $password);
            }

            $connectRequest  = new Request(
                self::CONNECT_ENDPOINT,
                Request::METHOD_POST,
                ['Content-Type' => 'application/json', 'Token' => $this->token],
                $this->serializer->serialize([
                    "USERNAME" => $username,
                    "PASSWORD" => $password
                ])
            );

            $this->synchronousRequest($connectRequest);
            $this->connected = true;
        }

        return $this;
    }

    /**
     * SIGN_IN: POST@partner.viettelpost.vn/v2/user/Login
     *
     * @param $username
     * @param $password
     *
     * @return string|void
     * @throws \Throwable
     */
    private function login($username, $password)
    {
        $body = $this->serializer->serialize([
            "USERNAME" => $username,
            "PASSWORD" => $password
        ]);
        $tokenRequest  = new Request(
            self::LOGIN_ENDPOINT,
            Request::METHOD_POST,
            ['Content-Type' => 'application/json'],
            $body
        );

        $response = $this->synchronousRequest($tokenRequest);
        if (isset($response['data']['token'])) {
            return $response['data']['token'];
        }
    }

    /**
     * PROVINCE: GET@partner.viettelpost.vn/v2/categories/listProvinceById
     *
     * @return array|bool|float|int|string|null
     * @throws \Throwable
     */
    public function getPublicProvinceCode()
    {
        $response =  $this->synchronousRequest(new Request(self::ALL_PROVINCE_ENDPOINT, Request::METHOD_GET, [], ""));
        array_walk($response['data'], function (&$value) {
            $value = array_change_key_case($value, CASE_LOWER);
        });

        return $response['data'];
    }

    /**
     * DISTRICT: GET@partner.viettelpost.vn/v2/categories/listDistrict
     *
     * @return array|bool|float|int|string|null
     * @throws \Throwable
     */
    public function getPublicDistrictCode()
    {
        $response = $this->synchronousRequest(new Request(self::ALL_DISTRICT_ENDPOINT, Request::METHOD_GET, [], ""));
        array_walk($response['data'], function (&$value) {
            $value = array_change_key_case($value, CASE_LOWER);
        });

        return $response['data'];
    }

    /**
     * WARD: GET@partner.viettelpost.vn/v2/categories/listWards
     *
     * @return array|bool|float|int|string|null
     * @throws \Throwable
     */
    public function getPublicWardCode()
    {
        $response = $this->synchronousRequest(new Request(self::ALL_WARD_ENDPOINT, Request::METHOD_GET, [], ""));
        array_walk($response['data'], function (&$value) {
            $value = array_change_key_case($value, CASE_LOWER);
        });

        return $response['data'];
    }

    /**
     * ALL_PRICE: POST@partner.viettelpost.vn/v2/order/getPriceAll
     *
     * @param $params
     * @return string
     * @throws \Throwable
     */
    public function getAllPrice($params)
    {
        $request = new Request(
            self::PRICING_ENDPOINT,
            Request::METHOD_POST,
            ['Content-Type' => 'application/json', 'Token' => $this->token],
            $this->serializer->serialize($params)
        );

        $response = $this->asyncClient->request($request)->get();
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $result = $this->serializer->unserialize($response->getBody());
            $message = $result['message'] ?? "Can't send request to Viettel Post API. Please try again.";
            throw new LocalizedException(__($message));
        }

        return $response->getBody();
    }

    /**
     * CREATE_ORDER: POST@partner.viettelpost.vn/v2/order/createOrder
     *
     * @param $params
     * @return array|bool|float|int|string|null
     * @throws \Throwable
     */
    public function createOrder($params)
    {
        $request = new Request(
            self::CREATE_ORDER_ENDPOINT,
            Request::METHOD_POST,
            ['Content-Type' => 'application/json', 'Token' => $this->token],
            $this->serializer->serialize($params)
        );

        return $this->synchronousRequest($request);
    }
}
