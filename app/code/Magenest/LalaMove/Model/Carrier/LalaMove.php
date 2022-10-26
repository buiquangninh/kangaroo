<?php

namespace Magenest\LalaMove\Model\Carrier;

use Exception;
use Magenest\RealShippingMethod\Setup\Patch\Data\UpdateOrderStatus;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Session;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Xml\Security;
use Magento\Inventory\Model\ResourceModel\Source;
use Magento\Inventory\Model\SourceFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory as RateFactory;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory as TrackErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackFactory;
use Psr\Log\LoggerInterface;
use Zend_Http_Client;
use Magenest\MapList\Model\DistanceProvider\Goong\GetLatLngFromAddress;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\Framework\App\RequestInterface;
class LalaMove extends AbstractCarrierOnline implements CarrierInterface
{
    const CODE = 'lalamove';
    /** @var string */
    protected $_code = 'lalamove';

    /** @var bool */
    protected $_isFixed = true;

    /** @var ZendClientFactory */
    protected $httpClientFactory;

    /** @var SourceFactory */
    protected $sourceFactory;

    /** @var Quote */
    protected $inventoryResource;

    /** @var MessageManager */
    protected $messageManager;

    /** @var ResourceOrder */
    protected $resourceOrder;

    /** @var OrderFactory */
    protected $orderFactory;

    /** @var Session */
    protected $checkoutSession;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var CacheInterface */
    protected $cache;

    /** @var NotifierInterface */
    protected $notifier;

    /** @var EventManager */
    protected $eventManager;

    /** @var TimezoneInterface */
    protected $timezone;

    protected $api;

    /**
     * @var \Magenest\CustomSource\Helper\Data
     */
    protected $areaData;

    private $getLatLngFromAddress;

    /**
     * @var AddressInterfaceFactory
     */
    private $address;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param RateErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param ElementFactory $xmlElFactory
     * @param RateFactory $rateFactory
     * @param MethodFactory $rateMethodFactory
     * @param TrackFactory $trackFactory
     * @param TrackErrorFactory $trackErrorFactory
     * @param StatusFactory $trackStatusFactory
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param CurrencyFactory $currencyFactory
     * @param Data $directoryData
     * @param StockRegistryInterface $stockRegistry
     * @param ZendClientFactory $httpClientFactory
     * @param Source $inventoryResource
     * @param SourceFactory $sourceFactory
     * @param MessageManager $messageManager
     * @param ResourceOrder $resourceOrder
     * @param OrderFactory $orderFactory
     * @param Session $session
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     * @param NotifierInterface $notifier
     * @param EventManager $eventManager
     * @param TimezoneInterface $timezone
     * @param \Magenest\CustomSource\Helper\Data $areaData
     * @param GetLatLngFromAddress $getLatLngFromAddress
     * @param AddressInterfaceFactory $address
     * @param RequestInterface $request
     * @param array $data
     * @param $
     */
    public function __construct(
        ScopeConfigInterface                $scopeConfig,
        RateErrorFactory                    $rateErrorFactory,
        LoggerInterface                     $logger,
        Security                            $xmlSecurity,
        ElementFactory                      $xmlElFactory,
        RateFactory                         $rateFactory,
        MethodFactory                       $rateMethodFactory,
        TrackFactory                        $trackFactory,
        TrackErrorFactory                   $trackErrorFactory,
        StatusFactory                       $trackStatusFactory,
        RegionFactory                       $regionFactory,
        CountryFactory                      $countryFactory,
        CurrencyFactory                     $currencyFactory,
        Data                                $directoryData,
        StockRegistryInterface              $stockRegistry,
        ZendClientFactory                   $httpClientFactory,
        Source                              $inventoryResource,
        SourceFactory                       $sourceFactory,
        MessageManager                      $messageManager,
        ResourceOrder                       $resourceOrder,
        OrderFactory                        $orderFactory,
        Session                             $session,
        SerializerInterface                 $serializer,
        CacheInterface                      $cache,
        NotifierInterface                   $notifier,
        EventManager                        $eventManager,
        TimezoneInterface                   $timezone,
        \Magenest\CustomSource\Helper\Data  $areaData,
        GetLatLngFromAddress                $getLatLngFromAddress,
        AddressInterfaceFactory             $address,
        RequestInterface                    $request,
        array                               $data = []
    )
    {
        $this->httpClientFactory = $httpClientFactory;
        $this->inventoryResource = $inventoryResource;
        $this->sourceFactory = $sourceFactory;
        $this->messageManager = $messageManager;
        $this->resourceOrder = $resourceOrder;
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $session;
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->notifier = $notifier;
        $this->eventManager = $eventManager;
        $this->timezone = $timezone;
        $this->getLatLngFromAddress = $getLatLngFromAddress;
        $this->request = $request;
        $this->address = $address;
        $this->areaData = $areaData;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @param array $data
     *
     * @throws AlreadyExistsException
     */
    public function updateShipmentStatus($data = [])
    {
        if (!empty($data) && isset($data['label_id']) && isset($data['partner_id'])) {
            $partnerId = $data['partner_id'];
            $order = $this->orderFactory->create();
            $this->resourceOrder->load($order, $partnerId, 'increment_id');
            if (!$order->getId()) {
                return;
            }
            $order->setApiOrderId($data['label_id']);
            $order->setShippingStatus($data['status_id']);
            if ($data['status_id'] == 5) {
                $order->setStatus(Order::STATE_COMPLETE)->setState(Order::STATE_COMPLETE);
                if ($order->getPayment()->getMethod() == "cashondelivery") {
                    $codAmount = $order->getGrandTotal();
                    $order->setTransferAmount($codAmount);
                    $order->setTransferDate($this->timezone->date()->format('Y-m-d H:i:s'));
                }
            } elseif ($data['status_id'] == -1) {
                $reason = null;
                if (isset($data['reason']) && $data['reason']) {
                    $reason = $data['reason'];
                }
            }
            $this->resourceOrder->save($order);
        }
    }

    public function generateUuidV4($data = null)
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for the time_low
            random_int(0, 0xffff), random_int(0, 0xffff),
            // 16 bits for the time_mid
            random_int(0, 0xffff),
            // 16 bits for the time_hi,
            random_int(0, 0x0fff) | 0x4000,
            // 8 bits and 16 bits for the clk_seq_hi_res,
            // 8 bits for the clk_seq_low,
            random_int(0, 0x3fff) | 0x8000,
            // 48 bits for the node
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

    /**
     * @param $params
     * @param $lalamoveLabelId
     */
    public function cancelOrder($lalamoveLabelId)
    {
        $path = '/v3/orders/'.$lalamoveLabelId;
        $uri = $this->getModeUrl().$path;
        $date = $this->timezone->date()->format('Uv');
        $key = $this->_scopeConfig->getValue('carriers/lalamove/api_key');
        $secret = $this->_scopeConfig->getValue('carriers/lalamove/api_secret');
        $body = $this->serializer->serialize((object) array());
        $method = 'DELETE';
        $stringSig = $date . "\r\n" . $method . "\r\n" . $path . "\r\n\r\n" . $body;
        $signature = hash_hmac('sha256', $stringSig, $secret);
        $token = "$key:$date:$signature";
        $responseApi = $this->apiCall('DELETE', $token, $uri, $body);
    }

    public function getModeUrl()
    {
        if ($this->getConfigData('test_mode')) {
            return $this->getConfigData('sandbox_api');
        } else {
            return $this->getConfigData('production_api');
        }
    }

    public function apiCall($method, $params, $uri, $body)
    {
//        $cacheData = $this->cache->load($cacheKey);
        $key = $this->_scopeConfig->getValue('carriers/lalamove/api_key');
        $secret = $this->_scopeConfig->getValue('carriers/lalamove/api_secret');
        $secret = $this->_scopeConfig->getValue('carriers/lalamove/api_secret');
        $client = $this->httpClientFactory->create();
        $client->setUri($uri);
        $client->setMethod($method);
        $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $nonce = $this->generateUuidV4();
        $client->setHeaders('Request-ID', $nonce);
        $client->setHeaders('Market', 'VN');
        $client->setHeaders('Authorization', 'hmac ' . $params);
        if ($method == 'GET') {
            $client->setParameterGet('',$body);
            $client->setParameterPost('', $body);
            $client->setRawData($body);
        } elseif ($method == 'POST') {
            $client->setParameterPost('', $body);
            $client->setRawData($body);
        } elseif ($method == 'DELETE'){
            $client->setParameterPost('',$body);
            $client->setRawData($body);
        }
        $response = $client->request();
        return $response->getBody();
    }

    public function processAdditionalValidation(DataObject $request)
    {
        return true;
    }

    public function isShippingLabelsAvailable()
    {
        return true;
    }

    public function collectRates($request)
    {
        if (!$this->getConfigFlag('active') || $this->checkoutSession->getInstallmentPaymentValue()) {
            return false;
        }

        $freeBoxes = $this->getFreeBoxesCount($request);

        $shippingPrice = $this->lalaMoveShippingPrice($request);

        $result = $this->_rateFactory->create();

        if ($shippingPrice !== false) {
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            if ($request->getPackageQty() == $freeBoxes || $request->getFreeShipping()) {
                $method = $this->_createCustomTableRateMethod($shippingPrice);
                $method->setOriginalPrice($shippingPrice);
                $method->setPrice(0);
                $method->setCost(0);
            } else {
                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);
            }
            $this->checkoutSession->setActiveShippingMethod($this->_code);
            $result->append($method);
        } else {
            $this->checkoutSession->setLoadInactive(true);
        }
        return $result;
    }

    private function _createCustomTableRateMethod($fee)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($fee);
        $method->setCost($fee);

        return $method;
    }

    private function getFreeBoxesCount(RateRequest $request)
    {
        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    $freeBoxes += $this->getFreeBoxesCountFromChildren($item);
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }
        return $freeBoxes;
    }

    /**
     * Returns free boxes count of children
     *
     * @param mixed $item
     *
     * @return mixed
     */
    private function getFreeBoxesCountFromChildren($item)
    {
        $freeBoxes = 0;
        foreach ($item->getChildren() as $child) {
            if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                $freeBoxes += $item->getQty() * $child->getQty();
            }
        }
        return $freeBoxes;
    }

    /**
     * @param $request
     * @return string|void
     */
    public function getServiceType($request)
    {
        $items = $request->getAllItems();
        $weight = 0;
        foreach ($items as $item) {
            if ($item->getWeight()) {
                $weight += ($item->getWeight() * $item->getQty() * 1000);
            }
        }
        if ($weight < 30000) {
            return "MOTORCYCLE";
        } else if ($weight > 30000 && $weight < 500000) {
            return "TRUCK175";
        } else if ($weight > 500000 && $weight < 1000000) {
            return "TRUCK330";
        } else if ($weight > 1000000 && $weight < 2000000) {
            return "TRUCK550";
        }
    }

    /**
     * @param $request
     * @return float|int|string
     */
    public function getTotalWeight($request)
    {
        $items = $request->getAllItems();
        $weight = 0;
        foreach ($items as $item) {
            if ($item->getWeight()) {
                $weight += ($item->getWeight() * $item->getQty() * 1000);
            }
        }
        if ($weight < 10000) {
            return "LESS_THAN_10_KG";
        }
        if (10000 < $weight && $weight < 20000) {
            return "10_KG_TO_20_KG";
        }
        if (20000 < $weight && $weight < 30000) {
            return "20_KG_TO_30_KG";
        } else return $weight;
    }

    /**
     * @param $request
     *
     * @return int|mixed
     */
    function vn_to_str($str)
    {

        $unicode = array(

            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',

            'd' => 'đ',

            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',

            'i' => 'í|ì|ỉ|ĩ|ị',

            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',

            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',

            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',

            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',

            'D' => 'Đ',

            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',

            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',

            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',

            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',

            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',


        );

        foreach ($unicode as $nonUnicode => $uni) {

            $str = preg_replace("/($uni)/i", $nonUnicode, $str);

        }
//        $str = str_replace(' ','_',$str);

        return $str;

    }

    public function lalaMoveShippingPrice($request)
    {
        $sourceItem = $this->sourceFactory->create();
        $areaCode = $this->areaData->getCurrentArea();
        if ($areaCode === 'mien_bac') {
            $this->inventoryResource->load($sourceItem, 'hy', 'source_code');
        } else {
            $this->inventoryResource->load($sourceItem, $areaCode, 'area_code');
        }


        $district = $request->getDestDistrict();
        if (is_array($district)) {
            $district = $district[1] ?? $district;
        }
        $country = $request->getData('country_id');
        $postcode = $request->getData('postcode');
        $street = $request->getData('dest_street');
        $region = $request->getData('dest_district');
        $city = $request->getData('city');
        $ward = $request->getData('dest_ward');
        $data = [
            'country' => $country,
            'postcode' => '',
            'street' => $street . ',' . $ward . ',' . $region . ',',
            'region' => '',
            'city' => $city,
        ];

        $address = $this->address->create($data);
        $lat = 0;
        $lng = 0;
        $res = $this->getLatLngFromAddress->execute($address);
        if ($res) {
            $lat = $res->getLat();
            $lng = $res->getLng();
        }
        $firstStop = $this->vn_to_str($sourceItem->getStreet() . ',' . $sourceItem->getData('ward') . ',' . $sourceItem->getData('district') . ',' . $sourceItem->getCity());
        $secondStop = $this->vn_to_str($street . ',' . $ward . ',' . $region . ',' . $city);
        $params = [
//            "scheduleAt" => $this->timezone->date()->format('c'),
            "serviceType" => $this->getServiceType($request),
            "specialRequests" => [],
            "language" => "vi_VN",
            "stops" => [
                [
                    "coordinates" => [
                        "lat" => strval($sourceItem->getLatitude()),
                        "lng" => strval($sourceItem->getLongitude()),
                    ],
                    "address" => $firstStop,
                ],
                [
                    "coordinates" => [
                        "lat" => strval($lat),
                        "lng" => strval($lng),
                    ],
                    "address" => $secondStop,
                ]
            ],
            "item" => [
                "quantity" => (string)count($request->getAllItems()),
                "weight" => $this->getTotalWeight($request),
            ],
            "isRouteOptimized" => true,
        ];
        $strParams = $this->serializer->serialize(['data' => $params]);
        $path = '/v3/quotations';
        $uri = $this->getModeUrl() . $path;
        $date = $this->timezone->date()->format('Uv');
        $key = $this->_scopeConfig->getValue('carriers/lalamove/api_key');
        $secret = $this->_scopeConfig->getValue('carriers/lalamove/api_secret');
        $method = 'POST';
//        $stringSig = $date.'\r\n.''POST'.$path.$strParams;
        $stringSig = $date . "\r\n" . $method . "\r\n" . $path . "\r\n\r\n" . $strParams;


        $signature = hash_hmac('sha256', $stringSig, $secret);


        $token = "$key:$date:$signature";
        $this->_logger->debug('Lalamove Get Quotaion Request: ' . $this->serializer->serialize($params));
        $responseApi = $this->apiCall('POST', $token, $uri, $strParams);
        $this->_logger->debug('Lalamove Get Quotaion Response: ' . $responseApi);
        $response = $this->serializer->unserialize($responseApi);
        try {
            if ($response) {
                $shippingPrice = $response['data']['priceBreakdown']['total'];
            } else $shippingPrice = 0;
            return $shippingPrice;
        }
        catch (\Exception $exception){

        }
    }

    public function getQuotation($request)
    {
        $sourceItem = $this->sourceFactory->create();
        $areaCode = $this->areaData->getCurrentArea();
        if ($areaCode === 'mien_bac') {
            $this->inventoryResource->load($sourceItem, 'hy', 'source_code');
        } else {
            $this->inventoryResource->load($sourceItem, $areaCode, 'area_code');
        }


        $district = $request->getDestDistrict();
        if (is_array($district)) {
            $district = $district[1] ?? $district;
        }
        $country = $request->getData('country_id');
        $postcode = $request->getData('postcode');
        $street = $request->getData('dest_street');
        $region = $request->getData('dest_district');
        $city = $request->getData('city');
        $ward = $request->getData('dest_ward');
        $data = [
            'country' => $country,
            'postcode' => '',
            'street' => $street . ',' . $ward . ',' . $region . ',',
            'region' => '',
            'city' => $city,
        ];

        $address = $this->address->create($data);
        $lat = 0;
        $lng = 0;
        $res = $this->getLatLngFromAddress->execute($address);
        if ($res) {
            $lat = $res->getLat();
            $lng = $res->getLng();
        }
        $firstStop = $this->vn_to_str($sourceItem->getStreet() . ',' . $sourceItem->getData('ward') . ',' . $sourceItem->getData('district') . ',' . $sourceItem->getCity());
        $secondStop = $this->vn_to_str($street . ',' . $ward . ',' . $region . ',' . $city);
        $params = [
//            "scheduleAt" => $this->timezone->date()->format('c'),
            "serviceType" => $this->getServiceType($request),
            "specialRequests" => [],
            "language" => "vi_VN",
            "stops" => [
                [
                    "coordinates" => [
                        "lat" => strval($sourceItem->getLatitude()),
                        "lng" => strval($sourceItem->getLongitude()),
                    ],
                    "address" => $firstStop,
                ],
                [
                    "coordinates" => [
                        "lat" => strval($lat),
                        "lng" => strval($lng),
                    ],
                    "address" => $secondStop,
                ]
            ],
            "item" => [
                "quantity" => (string)count($request->getAllItems()),
                "weight" => $this->getTotalWeight($request),
            ],
            "isRouteOptimized" => true,
        ];

        $body = $this->serializer->serialize($params);
        $strParams = $this->serializer->serialize(['data' => $params]);
        $uri = $this->getModeUrl();
        $date = $this->timezone->date()->format('Uv');
        $key = $this->_scopeConfig->getValue('carriers/lalamove/api_key');
        $secret = $this->_scopeConfig->getValue('carriers/lalamove/api_secret');
        $path = '/v3/quotations';
        $method = 'POST';
//        $stringSig = $date.'\r\n.''POST'.$path.$strParams;
        $stringSig = $date . "\r\n" . $method . "\r\n" . $path . "\r\n\r\n" . $strParams;


        $signature = hash_hmac('sha256', $stringSig, $secret);


        $token = "$key:$date:$signature";
        $responseApi = $this->apiCall('POST', $token, $uri, $strParams);
        $response = $this->serializer->unserialize($responseApi);
        return $response;
    }
    /**
     * Simplified shipping price calculation request
     *
     * @param $request
     *
     * @return array|bool|float|int|string|null
     */
    public function getRates($request)
    {
        $source = $request->getData('source_code');
        $street = $request->getData('street');
        $district = $request->getData('district');
        $city = $request->getCity();
        $ward = $request->getWard();
        $sourceLat = $request->getLat();
        $sourceLng = $request->getLng();
        $cusStreet = $request->getData('cusStreet')[0] ?? '';
        $cusWard = $request->getData('cusWard');
        $cusDistrict = $request->getData('cusDistrict');
        $cusCity = $request->getData('cusCity');
        $cusCountry = $request->getData('cusCountry');
        $order = $request->getData('order');
        $data = [
            'country' => $cusCountry,
            'postcode' => '',
            'street' => $cusStreet . ',' . $cusWard . ',' . $cusDistrict,
            'region' => '',
            'city' => $cusCity,
        ];
        $address = $this->address->create($data);
        $lat = 0;
        $lng = 0;
        $res = $this->getLatLngFromAddress->execute($address);
        if ($res) {
            $lat = $res->getLat();
            $lng = $res->getLng();
        }
        $firstStop = $this->vn_to_str($street . ',' . $ward . ',' .
            $district . ',' . $city);
        $secondStop = $this->vn_to_str($cusStreet . ',' . $cusWard . ',' . $cusDistrict . ',' . $cusCity);
        $params = [
//            "scheduleAt" => $this->timezone->date()->format('c'),
            "serviceType" => $this->getServiceType($order),
            "specialRequests" => [],
            "language" => "vi_VN",
            "stops" => [
                [
                    "coordinates" => [
                        "lat" => strval($sourceLat),
                        "lng" => strval($sourceLng),
                    ],
                    "address" => $firstStop,
                ],
                [
                    "coordinates" => [
                        "lat" => strval($lat),
                        "lng" => strval($lng),
                    ],
                    "address" => $secondStop,
                ]
            ],
            "item" => [
                "quantity" => (string)count($order->getAllItems()),
                "weight" => $this->getTotalWeight($order),
            ],
            "isRouteOptimized" => true,
        ];
        $body = $this->serializer->serialize($params);
        $strParams = $this->serializer->serialize(['data' => $params]);
        $path = '/v3/quotations';
        $uri = $this->getModeUrl() . $path;
        $date = $this->timezone->date()->format('Uv');
        $key = $this->_scopeConfig->getValue('carriers/lalamove/api_key');
        $secret = $this->_scopeConfig->getValue('carriers/lalamove/api_secret');
        $method = 'POST';
//        $stringSig = $date.'\r\n.''POST'.$path.$strParams;
        $stringSig = $date . "\r\n" . $method . "\r\n" . $path . "\r\n\r\n" . $strParams;


        $signature = hash_hmac('sha256', $stringSig, $secret);


        $token = "$key:$date:$signature";
        $this->_logger->debug('Lalamove Get Rates request: ' . $this->serializer->serialize($params));
        $responseApi = $this->apiCall('POST', $token, $uri, $strParams);
        $this->_logger->debug('Lalamove Get Rates response: ' . $responseApi);
        $response = $this->serializer->unserialize($responseApi);
        if ($response) {
            $this->cache->clean();
            $quotationId = $response['data']['quotationId'];
            $cacheKey = "Lalamove- "."{$secondStop}-"."{$quotationId}-"."{$request->getOrder()->getWeight()}";
            $this->cache->save($this->serializer->serialize($response), $cacheKey, ['config'], 3600);
//            $shippingPrice = $response['data']['priceBreakdown']['total'];
            $order->setData('quotation_id',$quotationId);
            $order->save();
            $newResponse = array_merge($response,['success'=>true]);
        }
        return $newResponse;
    }

    public function returnOfShipment($request)
    {
        $request->setIsReturn(true);
        $time = time();
        $data[] = [
            'tracking_number' => "NA-" . $time,
            'label_content' => "Not Active",
            'shipping_api_id' => "NA-" . $time
        ];

        if (!isset($isFirstRequest)) {
            $request->setMasterTrackingId("NA-" . $time);
            $isFirstRequest = false;
        }
        return new DataObject(['info' => $data]);
    }

    /**
     * @param DataObject $request
     *
     * @return DataObject
     * @throws AlreadyExistsException
     */
    protected function _doShipmentRequest(DataObject $request)
    {
        $result = new DataObject();
        if ($request->getIsReturn()) {
            $result->setTrackingNumber($request->getOrder()->getId());
        } else {
            $result->setTrackingNumber($request->getOrderShipment()->getOrderId());
        }
         $result->setShippingLabelContent('Lalamove Service');
        $packagesWeight = 0;
        if ($request->getIsReturn()
            || ($request->getOrderShipment()
                && !$request->getOrderShipment()->getOrder()->getApiOrderId())) {
            $packages = $request->getPackages();
            foreach ($packages as $package) {
                $packagesWeight += $package['params']['weight'];
            }
            $this->createOrder($request,$result,$packagesWeight);
        }

        return $result;
    }

    /**
     * @param $phone
     * @return mixed|string
     */
    public function convertPhoneNumber($phone){
        $length = strlen($phone);
        if ($phone[0] == 0) {
            $phone = substr($phone,1 - $length);
            $phone = "+84" . $phone;
        }
        return $phone;
    }


    /**
     * @param $request
     * @param $result
     * @param $quotation
     * @return void
     */
    public function createOrder($request, $result, $packagesWeight)
    {
        $order = $request->getIsReturn() ? $request->getOrder() : $request->getOrderShipment()->getOrder();
        $quotationId = $request->getOrderShipment()->getOrder()->getData('quotation_id');
        try {
            $packagesWeight = 0;
            $street = $order->getShippingAddress()->getStreet()['0'];
            $ward = $order->getShippingAddress()->getWard();
            $district = $order->getShippingAddress()->getDistrict();
            $city = $order->getShippingAddress()->getCity();
            $cusAddress = $this->vn_to_str($street.','.$ward.','.$district.','.$city);
            $cacheKey = "Lalamove- "."{$cusAddress}-"."{$quotationId}-"."{$order->getWeight()}";
            $cache = $this->cache->load($cacheKey);
            if ($cache){
                $data = $this->serializer->unserialize($this->cache->load($cacheKey));
                $orderId = $order->getId();
                $path = '/v3/orders';
                $uri = $this->getModeUrl() . $path;
                $date = $this->timezone->date()->format('Uv');
                $key = $this->_scopeConfig->getValue('carriers/lalamove/api_key');
                $secret = $this->_scopeConfig->getValue('carriers/lalamove/api_secret');
                $params = [
                    "quotationId" => $quotationId,
                    "sender" => [
                        "stopId" => $data['data']['stops']['0']['stopId'],
                        "name" => $this->vn_to_str($request->getData('shipper_contact_person_name')),
                        "phone" => $this->convertPhoneNumber($request->getData('shipper_contact_phone_number')),
                    ],
                    "recipients" => [
                        [
                            "stopId" => $data['data']['stops']['1']['stopId'],
                            "name" => $this->vn_to_str($request->getData('recipient_contact_person_name')),
                            "phone" => $this->convertPhoneNumber($request->getData('recipient_contact_phone_number')),
                        ]
                    ],
                ];
                $strParams = $this->serializer->serialize(['data' => $params]);
                $method = 'POST';
                $stringSig = $date . "\r\n" . $method . "\r\n" . $path . "\r\n\r\n" . $strParams;
                $signature = hash_hmac('sha256', $stringSig, $secret);
                $token = "$key:$date:$signature";
                $this->_logger->debug('Lalamove Create Order request: ' . $this->serializer->serialize($params));
                $requestApi = $this->apiCall('POST', $token, $uri, $strParams);
                $this->_logger->debug('Lalamove Create Order response: ' . $requestApi);
                $response = $this->serializer->unserialize($requestApi);
                if (!$response) {
                    if (!$request->getIsReturn()) {
                        $order->setState(Order::STATE_PROCESSING);
                        $this->resourceOrder->save($order);
                    }
                    $this->messageManager->addErrorMessage("Đơn hàng $quotationId sẽ được cập nhật sau do hệ thống Lalamove quá tải.");
                } elseif (isset($response['data'])) {
                    $shippingFee = $response['data']['priceBreakdown']['total'];
                    $orderCode = $response['data']['orderId'];
                    if (!$request->getIsReturn()) {
                        $order->setData('quotation_id', $orderCode);
                        $order->setData('shipping_fee', $shippingFee);
                        $order->setShippingStatus($response['data']['status']);
                        $order->setState(Order::STATE_PROCESSING);
                        $order->setStatus(UpdateOrderStatus::PROCESSING_SHIPMENT_STATUS);
                        $this->resourceOrder->save($order);
                        $this->eventManager->dispatch("create_Lalamove_success", ['order' => $order]);
                    }
                    $result->setShippingApiId($orderCode);
                    $this->messageManager->addSuccessMessage(__("Create Lalamove Order %1 Success", $quotationId));
                    $this->cache->clean();
                } else {
                    throw new LocalizedException(__($response['message']));
                }
            }
        } catch (Exception $e) {
            $this->notifier->addNotice(
                __('Submit Lalamove'),
                __(
                    "Unable to submit Lalamove for order: %1. Error: %2",
                    $order->getIncrementId(),
                    $e->getMessage()
                )
            );
            $this->eventManager->dispatch(
                "order_action_save_comment_history",
                [
                    'order' => $order,
                    'comment' => __("Create Lalamove Order Fail: %1", $e->getMessage())
                ]
            );
            $result->setErrors(__("Create Lalamove Order %1 Fail: %2", $quotationId, $e->getMessage()));
        }
    }

    /**
     * @param $item
     * @param $data
     * @param $weight
     *
     * @return void
     */
    private function processWeight($item, &$weight)
    {
        $itemWeight = (float)$item->getWeight() != 0 ? $item->getWeight() : 0.3;
        $weight += ($itemWeight * $item->getQtyOrdered());
    }

    /**
     * @param $item
     * @param $data
     * @param $totalWeight
     * @return void
     */
    private function processItem($item, &$data, $totalWeight)
    {
        $itemWeight = (float)$item->getWeight() != 0 ? $item->getWeight() : 0.3;
        $result = [
            "name" => $item->getName(),
            "weight" => $itemWeight * $item->getQtyOrdered(),
            "quantity" => (int)$item->getQtyOrdered(),
            "product_code" => "",
        ];

        if ($this->isTotalWeightGreaterThan20($totalWeight)) {
            $result['product_code'] = $item->getSku();
            $result['height'] = $item->getHeight() ?? null;
            $result['width'] = $item->getWidth() ?? null;
            $result['length'] = $item->getLength() ?? null;
        }

        $data[] = $result;
    }

    /**
     * @param $totalWeight
     * @return bool
     */
    private function isTotalWeightGreaterThan20($totalWeight)
    {
        return $totalWeight > 20;
    }

}
