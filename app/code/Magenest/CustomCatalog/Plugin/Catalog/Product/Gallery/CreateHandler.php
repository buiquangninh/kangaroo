<?php
/**
 * Copyright © 2021 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_CustomCatalog extension
 * NOTICE OF LICENSE
 *
 * @author   PhongNguyen
 * @category Magenest
 * @package  Magenest_CustomCatalog
 */

namespace Magenest\CustomCatalog\Plugin\Catalog\Product\Gallery;

use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter;
use Magento\Catalog\Model\ImageUploader;

/**
 * Class CreateHandler
 *
 * @package Magenest\CustomCatalog\Plugin\Catalog\Product\Gallery
 */
class CreateHandler extends \Magento\ProductVideo\Model\Plugin\Catalog\Product\Gallery\CreateHandler
{
    /**
     * @var ImageUploader
     */
    protected $_imageUploader;

    /**
     * CreateHandler constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel
     * @param ImageUploader                                        $imageUploader
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel,
        ImageUploader $imageUploader
    ) {
        $this->_imageUploader = $imageUploader;
        parent::__construct($resourceModel);
    }

    /**
     * @param \Magento\Catalog\Model\Product\Gallery\CreateHandler $mediaGalleryCreateHandler
     * @param \Magento\Catalog\Model\Product                       $product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function afterExecute(
        \Magento\Catalog\Model\Product\Gallery\CreateHandler $mediaGalleryCreateHandler,
        \Magento\Catalog\Model\Product $product
    ) {
        $mediaCollection = $this->getMediaEntriesDataCollection(
            $product,
            $mediaGalleryCreateHandler->getAttribute()
        );

        if (!empty($mediaCollection)) {
            if ($product->getIsDuplicate() === true) {
                $mediaCollection = $this->makeAllNewVideos($product->getId(), $mediaCollection);
            }
            $newVideoCollection = $this->collectNewVideos($mediaCollection);

            foreach ($newVideoCollection as $item) {
                if (isset($item['video_url'])) {
                    $this->_imageUploader->moveFileFromTmp($item['video_url'], true);
                }
            }

            $this->saveVideoData($newVideoCollection, 0);
            $videoDataCollection = $this->collectVideoData($mediaCollection);
            $this->saveVideoData($videoDataCollection, $product->getStoreId());
            $this->saveAdditionalStoreData($videoDataCollection);
        }

        return $product;
    }

    /**
     * Collect new videos
     *
     * @param array $mediaCollection
     *
     * @return array
     */
    private function collectNewVideos(array $mediaCollection): array
    {
        $return = [];
        foreach ($mediaCollection as $item) {
            if ($this->isVideoItem($item) && $this->isNewVideo($item)) {
                $return[] = $this->extractVideoDataFromRowData($item);
            }
        }
        return $return;
    }

    /**
     * Checks if gallery item is video
     *
     * @param array $item
     *
     * @return bool
     */
    private function isVideoItem(array $item): bool
    {
        return !empty($item['media_type'])
               && empty($item['removed'])
               && $item['media_type'] == ExternalVideoEntryConverter::MEDIA_TYPE_CODE;
    }

    /**
     * Checks if video is new
     *
     * @param array $item
     *
     * @return bool
     */
    private function isNewVideo(array $item): bool
    {
        return !isset($item['video_url_default'], $item['video_title_default'])
               || empty($item['video_url_default'])
               || empty($item['video_title_default'])
                || $item['video_url'] != $item['video_url_default'];
    }

    /**
     * Mark all videos as new
     *
     * @param int   $entityId
     * @param array $mediaCollection
     *
     * @return array
     */
    private function makeAllNewVideos($entityId, array $mediaCollection): array
    {
        foreach ($mediaCollection as $key => $video) {
            if ($this->isVideoItem($video)) {
                unset($video['video_url_default'], $video['video_title_default']);
                $video['entity_id']    = $entityId;
                $mediaCollection[$key] = $video;
            }
        }
        return $mediaCollection;
    }
}
