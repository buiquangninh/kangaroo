<?php


namespace Magenest\Affiliate\Observer;


use Magenest\Affiliate\Helper\Data as DataHelper;
use Magenest\Affiliate\Model\Account\Status;
use Magenest\Affiliate\Model\Email;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magenest\Directory\Helper\Data;

class CreateLimitedAffiliateAccount implements ObserverInterface
{
    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var Email
     */
    protected $email;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * CreateLimitedAffiliateAccount constructor.
     * @param DataHelper $dataHelper
     * @param CookieManagerInterface $cookieManager
     * @param RequestInterface $request
     * @param Email $email
     * @param Data $directoryHelper
     */
    public function __construct(
        DataHelper $dataHelper,
        CookieManagerInterface $cookieManager,
        RequestInterface $request,
        Email $email,
        Data $directoryHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->cookieManager = $cookieManager;
        $this->email = $email;
        $this->directoryHelper = $directoryHelper;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getCustomer();
        $account = $this->dataHelper->getCurrentAffiliate();

        $postValue = $this->request->getPostValue();

        $data = [];

        $data['customer_id'] = $customer->getId();
        $signUpConfig        = $this->dataHelper->getAffiliateAccountSignUp();
        $data['group_id']    = $signUpConfig['default_group'];

        if (isset($postValue['referred_by'])) {
            /** @var \Magenest\Affiliate\Model\Account $parent */
            $parent = $this->dataHelper->getAffiliateByEmailOrCode(strtolower(trim($postValue['referred_by'])));
            $data['parent']       = $parent->getId();
            $data['parent_email'] = $parent->getCustomer()->getEmail();
        } else {
            $parentKey = $this->cookieManager->getCookie('affiliate_key');
            $parent = $this->dataHelper->getAffiliateByKeyOrCode($parentKey);
            $data['parent']       = $parent->getId();
            $data['parent_email'] = $parent->getCustomer()->getEmail();
        }
        $data['status']             = Status::ACTIVE;
        $data['email_notification'] = $signUpConfig['default_email_notification'];
        $data['is_limited']         = 1;
        $data['telephone']          = $customer->getCustomAttribute('telephone') ? $customer->getCustomAttribute('telephone')->getValue() : false;
        $cityLabel = $districtLabel = $wardLabel = false;
        $cityId = $districtId = $wardId = false;
        if ($city = $customer->getCustomAttribute('city_id')) {
            $cityId = $city->getValue();
            $cityData = $this->directoryHelper->getCityById($cityId);
            $cityLabel = $cityData ? $cityData['full_name'] : false;
            if ($district = $customer->getCustomAttribute('district_id')) {
                $districtId = $district->getValue();
                $districtData = $this->directoryHelper->getDistrictById($cityId, $districtId);
                $districtLabel = $districtData ? $districtData['full_name'] : false;
                if ($ward = $customer->getCustomAttribute('ward_id')) {
                    $wardId = $ward->getValue();
                    $wardData = $this->directoryHelper->getWardById($cityId, $districtId, $wardId);
                    $wardLabel = $wardData ? $wardData['full_name'] : false;
                }
            }
        }

        $data['city']               = $cityLabel;
        $data['city_id']            = $cityId;
        $data['district']           = $districtLabel;
        $data['district_id']        = $districtId;
        $data['ward']               = $wardLabel;
        $data['ward_id']            = $wardId;

        try {
            $account->addData($data)->save();
            if ($this->dataHelper->isEnableAffiliateSignUpEmail()) {
                $this->email->sendEmailToAdmin(['customer' => $customer], DataHelper::XML_PATH_EMAIL_SIGN_UP_TEMPLATE);
            }
        } catch (\Exception $e) {
        }
    }
}
