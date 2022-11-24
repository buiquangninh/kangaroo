<?php

namespace Magenest\Security\Plugin;

use Magento\Backend\App\ConfigInterface;
use Magento\User\Model\ResourceModel\User;
use Mageplaza\Security\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class LockUser
 *
 * @package Mageplaza\Security\Plugin
 */
class UpdateBlackList
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var ConfigInterface
     */
    protected $_backendConfig;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * LockUser constructor.
     *
     * @param ConfigInterface $backendConfig
     * @param LoggerInterface $logger
     * @param \Magento\Framework\HTTP\PhpEnvironment\Request $request
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param Data $helper
     */
    public function __construct(
        ConfigInterface $backendConfig,
        LoggerInterface $logger,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        Data $helper
    ) {
        $this->_backendConfig = $backendConfig;
        $this->_logger = $logger;
        $this->_helper = $helper;
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @param User $userModel
     * @param $user
     * @param $setLockExpires
     * @param $setFirstFailure
     *
     */
    public function beforeUpdateFailure(User $userModel, $user, $setLockExpires, $setFirstFailure)
    {
        if ($this->_helper->isEnabled()
            && $this->_helper->getConfigBruteForce('enabled')
        ) {
            $maxFailures = $this->_backendConfig->getValue('admin/security/lockout_failures');
            if ($setLockExpires && (($user->getFailuresNum() + 1) === (int)$maxFailures)) {
                $ip = $this->request->getClientIp();
                $blackList = $this->_helper->getConfigBlackWhiteList('black_list');
                $arrBlackList = $blackList ? explode(',', $blackList) : [];
                if (!in_array($ip, $arrBlackList)) {
                    $arrBlackList[] = $ip;
                    $blackList = implode(',', $arrBlackList);
                    $this->configWriter->save('security/black_white_list/black_list', $blackList);
                    $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
                }
            }
        }
    }
}
