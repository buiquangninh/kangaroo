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

namespace Magenest\CustomCatalog\Block\Adminhtml\Edit\Tab;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Backend\Helper\Data as HelperBackend;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Magento\Backend\Block\Admin\Formkey;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\UrlInterface;
use Magenest\CustomCatalog\Controller\Adminhtml\Video\Upload;

/**
 * Class Video
 *
 * @package Magenest\CustomCatalog\Block\Adminhtml\Edit\Tab
 */
class Video extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    const PATH_VIDEO_UPLOAD = 'productvideo/video/upload';
    /**
     * @var Escaper
     */
    protected $_escaper;
    /**
     * @var Filesystem
     */
    protected $_filesystem;
    /**
     * @var DriverFile
     */
    protected $_file;
    /**
     * @var Formkey
     */
    protected $_formKey;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var RequestHttp
     */
    protected $_request;
    /**
     * @var HelperBackend
     */
    protected $_helperBackend;
    /**
     * @var array
     */
    protected $_data;
    /**
     * @var array
     */
    protected $_allowedExtensions;
    /**
     * @var array
     */
    protected $_sizeLimit;

    /**
     * Video constructor.
     *
     * @param ElementFactory           $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper                  $escaper
     * @param StoreManagerInterface    $storeManager
     * @param HelperBackend            $helperBackend
     * @param Filesystem               $filesystem
     * @param RequestHttp              $request
     * @param Formkey                  $formKey
     * @param DriverFile               $file
     * @param array                    $data
     * @param string                   $allowedExtensions
     * @param string                   $sizeLimit
     */
    public function __construct(
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        StoreManagerInterface $storeManager,
        HelperBackend $helperBackend,
        Filesystem $filesystem,
        RequestHttp $request,
        Formkey $formKey,
        DriverFile $file,
        $data = [],
        $allowedExtensions = '',
        $sizeLimit = ''
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_data              = $data;
        $this->_filesystem        = $filesystem;
        $this->_file              = $file;
        $this->_helperBackend     = $helperBackend;
        $this->_formKey           = $formKey;
        $this->_storeManager      = $storeManager;
        $this->_request           = $request;
        $this->_allowedExtensions = $allowedExtensions;
        $this->_sizeLimit         = $sizeLimit;
    }

    public function getElementHtml()
    {
        $this->setType('hidden');
        $uploadAction      = $this->_helperBackend->getUrl(self::PATH_VIDEO_UPLOAD);
        $mediaUrl          = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $uploadFolderPath  = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $sizeLimit         = $this->getSizeLimit();
        $allowedExtensions = implode('","', $this->getAllowedExtensions());
        $html              = '<div class="admin__field-control control">';
        $html              .= '<script type="text/javascript">
				require([
					"jquery",
					"jquery/ui",
					"catalogUploader"
				], function(jQuery){
					(function($) {
						$(document).ready(function() {
							videoUploader = new qq.FileUploader({
								element: document.getElementById(\'market_video\'),
								action: "' . $uploadAction . '",
								params: {"form_key":"' . $this->_formKey->getFormKey() . '", "product_video": true},
								multiple: false,
								allowedExtensions: ["' . $allowedExtensions . '"],
								sizeLimit: ' . $sizeLimit . ',
								onComplete: function(id, fileName, responseJSON){
									if (responseJSON.success)
									{
										if ($(\'.mage-new-video-dialog\').find(\'.video-player-container\').children(\'video\'))
										{
										  $.each($(\'.mage-new-video-dialog\').find(\'.video-player-container\').children(),function(index) {
											$(this).remove();
										  });
										}
									   $(\'.mage-new-video-dialog\').find(\'.video-player-container\')
									   .append(\'<video id="product-video" autoplay muted loop width="100%" height="100%" playsinline>';
        $html              .= '<source src="' . $mediaUrl . Upload::PATH_TMP_CATALOG_PRODUCT_VIDEO . '\'+responseJSON.name+\'" type="\'+responseJSON.type+\'"></video>\');
                                        $(\'.mage-new-video-dialog\').find(\'.video-player-container\').addClass(\'no-before\');
										if ($(\'#advice-required-entry-video\'))
										{
											$(\'#advice-required-entry-video\').remove();
										}
										$(\'#product-video\').load(function(){
										   initHotspotBtn();
										});
										$(\'#video_path\').val(responseJSON.name);
										$(\'#video_path\').removeClass(\'validation-failed\');
									}
								}
							});
						});
					})(jQuery);
				});
                </script>';
        if ($this->getValue()) {
            $img_src  = $mediaUrl . $this->getValue();
            $img_path = $uploadFolderPath . $this->getValue();
            if ($this->_file->isExists($img_path)) {
                $html .= '<img id="product-video" src="' . $img_src . '" />';
            } else {
                $html .= '<h4 id="product-video" style="color:red;">File ' . $img_src . ' doesn\'t exists.</h4>';
            }
        }
        $html .= '<div id="market_video">
                    <noscript>
                        <p>Please enable JavaScript to use file uploader.</p>
                        <!-- or put a simple form for upload here -->
                    </noscript>
                </div>';
        $html .= parent::getElementHtml();
        $html .= '<p class="note" style="clear:both; float:left;">Allowed file extensions: ' . implode(',', $this->getAllowedExtensions()) . '</p>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @return string
     */
    protected function getAllowedExtensions()
    {
        return $this->_allowedExtensions;
    }

    /**
     * @return int
     */
    protected function getSizeLimit()
    {
        return (int)$this->_sizeLimit;
    }
}
