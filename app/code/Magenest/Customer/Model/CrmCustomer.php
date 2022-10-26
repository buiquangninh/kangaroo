<?php

namespace Magenest\Customer\Model;

use Magenest\Customer\Api\CrmCustomerInterface;
use Magenest\Customer\Helper\Login;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Model\Oauth\TokenFactory;
use Psr\Log\LoggerInterface;

class CrmCustomer implements CrmCustomerInterface
{
    /**
     * @var CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var SessionCleanerInterface
     */
    private $sessionCleaner;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * CrmCustomer constructor.
     * @param CollectionFactory $customerCollectionFactory
     * @param CustomerRegistry $customerRegistry
     * @param TokenFactory $tokenFactory
     * @param LoggerInterface $logger
     * @param AddressRegistry $addressRegistry
     * @param CredentialsValidator $credentialsValidator
     * @param Encryptor $encryptor
     * @param SessionCleanerInterface $sessionCleaner
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CollectionFactory $customerCollectionFactory,
        CustomerRegistry $customerRegistry,
        TokenFactory $tokenFactory,
        LoggerInterface $logger,
        AddressRegistry $addressRegistry,
        CredentialsValidator $credentialsValidator,
        Encryptor $encryptor,
        SessionCleanerInterface $sessionCleaner,
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerRegistry          = $customerRegistry;
        $this->tokenFactory              = $tokenFactory;
        $this->logger                    = $logger;
        $this->addressRegistry           = $addressRegistry;
        $this->credentialsValidator      = $credentialsValidator;
        $this->encryptor                 = $encryptor;
        $this->sessionCleaner            = $sessionCleaner;
        $this->customerRepository        = $customerRepository;
    }

    /**
     * @param $telephone
     *
     * @return array|false|mixed|null
     */
    public function authenticateByTelephone($telephone)
    {
        try {
            $telephoneModified = Login::modifyMobileNumber($telephone);
            $collection        = $this->customerCollectionFactory->create()
                ->addAttributeToFilter('telephone', $telephoneModified)
                ->setPageSize(1)->setCurPage(1);
            if ($collection->getSize() == 1) {
                return $collection->getFirstItem()->getData('email');
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        return false;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function phoneLogin(string $telephone): string
    {
        $email = $this->authenticateByTelephone($telephone);
        if (!$email) {
            throw new LocalizedException(__("Phone number isn't found"));
        }

        $customer = $this->customerRegistry->retrieveByEmail($email);
        return $this->tokenFactory->create()->createCustomerToken($customer->getId())->getToken();
    }

    /**
     * @param string $telephone
     * @param string $password
     * @return bool
     * @throws InputException
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function changePassword(string $telephone, string $password): bool
    {
        $email = $this->authenticateByTelephone($telephone);
        if (!$email) {
            throw new LocalizedException(__("Phone number isn't found"));
        }

        $customer = $this->customerRegistry->retrieveByEmail($email);
        $this->credentialsValidator->checkPasswordDifferentFromEmail($email, $password);
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $customerSecure->setPasswordHash($this->createPasswordHash($password));
        $this->sessionCleaner->clearFor((int)$customer->getId());
        $this->disableAddressValidation($customer);
        $this->customerRepository->save($customer);

        return true;
    }

    /**
     * Disable Customer Address Validation
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }

    /**
     * Create a hash for the given password
     *
     * @param string $password
     * @return string
     */
    protected function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * @param $customerId
     * @return bool|CustomerInterface|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function inactivateById($customerId): bool
    {
        // TODO: Implement inactivateById() method.
        $customer = $this->customerRepository->getById($customerId);
        $customer->setEmail("disabled-".time()."-". $customer->getEmail());
        try {
            $this->customerRepository->save($customer);
            return true;
        } catch (\Exception $e) {
            throw new \Exception(__('Cannot deactivate this customer for now. Please try again later!'));
        }
    }
}
