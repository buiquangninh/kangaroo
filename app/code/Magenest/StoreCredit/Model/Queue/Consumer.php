<?php
namespace Magenest\StoreCredit\Model\Queue;

use Magenest\StoreCredit\Model\TransactionFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;

class Consumer
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var CustomerRegistry */
    private $customerRegistry;

    /** @var TransactionFactory */
    private $transactionFactory;

    /**
     * @param SerializerInterface $serializer
     * @param TransactionFactory $transactionFactory
     * @param CustomerRegistry $customerRegistry
     */
    public function __construct(
        SerializerInterface $serializer,
        TransactionFactory  $transactionFactory,
        CustomerRegistry    $customerRegistry
    ) {
        $this->serializer       = $serializer;
        $this->customerRegistry = $customerRegistry;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @param $transactionData
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process($transactionData)
    {
        $transaction = $this->serializer->unserialize($transactionData);
        $this->transactionFactory->create()->processTransaction(
            $transaction['code'],
            $this->customerRegistry->retrieve($transaction['customer']),
            new DataObject($transaction['action_object'])
        );
    }
}
