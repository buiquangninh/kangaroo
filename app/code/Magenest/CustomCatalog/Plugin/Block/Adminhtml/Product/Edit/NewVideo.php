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

namespace Magenest\CustomCatalog\Plugin\Block\Adminhtml\Product\Edit;

use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\UrlInterface;
use Magento\Framework\Registry;

/**
 * Class NewVideo
 *
 * @package Magenest\CustomCatalog\Plugin\Block\Adminhtml\Product\Edit
 */
class NewVideo
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * NewVideo constructor.
     *
     * @param UrlInterface $urlBuilder
     * @param Registry     $coreRegistry
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Registry $coreRegistry
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @param \Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo $subject
     * @param                                                             $form
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetForm(
        \Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo $subject,
        $form
    ) {
        /** @var Fieldset $fieldset */
        $fieldset = $form->getElement('new_video_form_fieldset');
        $fieldset->addType('productvideo', '\Magenest\CustomCatalog\Block\Adminhtml\Edit\Tab\Video');
        $fieldset->removeField('video_url');
        $fieldset->removeField('new_video_get');
        $fieldset->removeField('new_video_screenshot');
        $fieldset->removeField('role-label');
        $fieldset->removeField('new_video_disabled');
        $mediaRoles = $this->getProduct($subject)->getMediaAttributes();
        foreach ($mediaRoles as $mediaRole) {
            $fieldset->removeField("video_{$mediaRole->getAttributeCode()}");
        }
        $fieldset->addField(
            'video_path',
            'productvideo',
            [
                'label'    => __('Upload'),
                'name'     => 'video_path',
                'required' => true
            ],
            'video_provider'
        );
        $fieldset->addField(
            'new_video_screenshot',
            'file',
            [
                'label'    => __('Preview Image'),
                'title'    => __('Preview Image'),
                'name'     => 'image',
                'required' => true
            ],
            'video_description'
        );
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct($subject)
    {
        if (!$subject->hasData('product')) {
            $subject->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $subject->getData('product');
    }
}
