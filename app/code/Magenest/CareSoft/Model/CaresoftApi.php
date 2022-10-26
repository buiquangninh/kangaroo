<?php
namespace Magenest\CareSoft\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class CaresoftApi
{
    const BASE_URL = "https://api.caresoft.vn";
    const TICKET_ENDPOINT = "/kangaroo/api/v1/tickets";
    const GROUP_ENDPOINT = "/kangaroo/api/v1/groups";
    const TICKET_CUSTOM_FIELDS_ENDPOINT = "/kangaroo/api/v1/tickets/custom_fields";

    const STATUS_CONFIG = "caresoft/general/enable";
    const TOKEN_CONFIG = "caresoft/general/token";
    const GROUP_CONFIG = "caresoft/general/group";
    const CUSTOM_FIELD_CONFIG = "caresoft/general/order_custom_field";

    /** @var AsyncClientInterface */
    private $asyncClient;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var SerializerInterface */
    private $serializer;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param AsyncClientInterface $asyncClient
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        AsyncClientInterface $asyncClient,
        ScopeConfigInterface $scopeConfig,
        SerializerInterface  $serializer,
        LoggerInterface      $logger
    ) {
        $this->asyncClient = $asyncClient;
        $this->scopeConfig = $scopeConfig;
        $this->serializer  = $serializer;
        $this->logger      = $logger;
    }

    private function synchronousRequest($url, $method, $body = null)
    {
        try {
            $token = $this->scopeConfig->getValue(self::TOKEN_CONFIG);
            if (empty($token)) {
                throw new LocalizedException(__("Caresoft Authorization Token not found."));
            }

            $request = new Request(
                $url,
                $method,
                ['Content-Type' => 'application/json', 'Authorization' => "Bearer $token"],
                $body
            );

            $response = $this->asyncClient->request($request)->get();
            $result   = $this->serializer->unserialize($response->getBody());
            if ($response->getStatusCode() != 200 || (isset($result['code']) && $result['code'] !== 'ok')) {
                throw new LocalizedException(__("Request unsuccessfully."));
            }

            return $result;
        } catch (\Throwable $e) {
            $this->logger->critical(
                $e->getMessage(),
                [
                    'trace' => $e->getTraceAsString(),
                    'body' => $body ?? '',
                    'response' => isset($response) ? $response->getBody() : ''
                ]
            );

            return [];
        }

    }

    /**
     * @param $payload
     */
    public function createTicket($payload)
    {
        try {
            if (empty($payload['ticket']['group_id'])) {
                throw new LocalizedException(__("Caresoft Group haven't been set in Configuration."));
            }

            $this->synchronousRequest(
                self::BASE_URL . self::TICKET_ENDPOINT,
                Request::METHOD_POST,
                $this->serializer->serialize($payload)
            );
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * @return array
     */
    public function getGroup()
    {
        $result = $this->synchronousRequest(self::BASE_URL . self::GROUP_ENDPOINT, Request::METHOD_GET);
        return $result['groups'] ?? [];
    }

    /**
     * @return array
     */
    public function getCustomFields()
    {
        $result = $this->synchronousRequest(self::BASE_URL . self::TICKET_CUSTOM_FIELDS_ENDPOINT, Request::METHOD_GET);
        return $result['custom_fields'] ?? [];
    }
}
