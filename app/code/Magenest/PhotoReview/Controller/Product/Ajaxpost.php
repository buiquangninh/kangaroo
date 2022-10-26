<?php

namespace Magenest\PhotoReview\Controller\Product;

use Exception;
use Magenest\PhotoReview\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Session\Generic;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductRepository;

class Ajaxpost extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var SerializerInterface
     */
    private $serialize;
    /**
     * @var ReviewFactory
     */
    protected ReviewFactory $reviewFactory;
    /**
     * @var Session
     */
    protected Session $customerSession;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;
    /**
     * @var RatingFactory
     */
    protected RatingFactory $ratingFactory;
    /**
     * @var Generic
     */
    protected Generic $reviewSession;
    /**
     * @var Registry
     */
    protected Registry $coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Http
     */
    protected $http;

    protected ProductRepository $productRepository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Session $customerSession
     * @param ReviewFactory $reviewFactory
     * @param RatingFactory $ratingFactory
     * @param Generic $reviewSession
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     * @param PageFactory $resultPageFactory
     * @param Json $json
     * @param LoggerInterface $logger
     * @param Http $http
     * @param SerializerInterface $serialize
     */
    public function __construct(
        Context                     $context,
        Registry                    $coreRegistry,
        Session                     $customerSession,
        ReviewFactory               $reviewFactory,
        RatingFactory               $ratingFactory,
        Generic                     $reviewSession,
        StoreManagerInterface       $storeManager,
        Data                        $dataHelper,
        PageFactory                 $resultPageFactory,
        Json                        $json,
        LoggerInterface             $logger,
        Http                        $http,
        ProductRepository           $productRepository,
        SerializerInterface         $serialize
    ) {
        $this->productRepository = $productRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->serializer = $json;
        $this->logger = $logger;
        $this->http = $http;
        $this->coreRegistry = $coreRegistry;
        $this->reviewSession = $reviewSession;
        $this->ratingFactory = $ratingFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->reviewFactory = $reviewFactory;
        $this->dataHelper = $dataHelper;
        $this->serialize = $serialize;
        parent::__construct($context);
    }

    /**
     * Submit new review action
     *
     * @return Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $countRatings = $data['count_ratings'];
        unset($data['count_ratings']);
        $rating = $this->getRequest()->getParam('ratings', []);


        for ($i = 1; $i <= $countRatings; $i++) {
            $productId = $data['product_id_'. $i];
            $data1 = $data;
            $data1['title_' . $i] = implode(',', array_keys($data['title_' . $i]));
            $data1['title'] = $data1['title_' . $i];
            $data1['detail'] = $data1['details_' . $i];
            $rating1[2] = $rating[$i];
            $_FILES['photo'][$i] = $_FILES['photo_' . $i] ?? [];
            $_FILES['video'][$i] = $_FILES['video_' . $i] ?? [];
            if (!empty($data1) && $product = $this->productRepository->getById($productId)) {
                /** @var Review $review */
                $review = $this->reviewFactory->create()->setData($data1);
                $review->unsetData('review_id');

                $validate = $this->validate($review);
                if ($validate === true) {
                    try {
                        if ($this->dataHelper->isEnableAutoApproved()) {
                            $review->setStatusId(Review::STATUS_APPROVED);
                        } else {
                            $review->setStatusId(Review::STATUS_PENDING);
                        }

                        $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                            ->setEntityPkValue($product->getId())
                            ->setCustomerId($this->customerSession->getCustomerId())
                            ->setStoreId($this->storeManager->getStore()->getId())
                            ->setStores([$this->storeManager->getStore()->getId()])
                            ->save();

                        foreach ($rating1 as $ratingId => $optionId) {
                            $this->ratingFactory->create()
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->setCustomerId($this->customerSession->getCustomerId())
                                ->addOptionVote($optionId, $product->getId());
                        }

                        $review->aggregate();

                        unset($_FILES['photo'][$i]);
                        unset($_FILES['video'][$i]);

                        if ($this->dataHelper->isEnableAutoApproved()) {
                            $this->messageManager->addSuccessMessage(__('Thank you for rating!'));
                        } else {
                            $this->messageManager->addSuccessMessage(__('You submitted your review for moderation.'));
                        }

                    } catch (Exception $e) {
                        $this->reviewSession->setFormData($data1);
                        $this->messageManager->addErrorMessage(__('We can\'t post your review right now.'));
                    }
                } else {
                    $this->reviewSession->setFormData($data1);
                    if (is_array($validate)) {
                        foreach ($validate as $errorMessage) {
                            $this->messageManager->addErrorMessage($errorMessage);
                        }
                    } else {
                        $this->messageManager->addErrorMessage(__('We can\'t post your review right now.'));
                    }
                }
            }
        }
    }

    /**
     * Validate review summary fields
     * @param $review
     * @return bool|string[]
     * @throws \Zend_Validate_Exception
     */
    private function validate($review)
    {
        $errors = [];

        if (!\Zend_Validate::is($review->getNickname(), 'NotEmpty')) {
            $errors[] = __('Please enter a nickname.');
        }

        if (!\Zend_Validate::is($review->getDetail(), 'NotEmpty')) {
            $errors[] = __('Please enter a review.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}
