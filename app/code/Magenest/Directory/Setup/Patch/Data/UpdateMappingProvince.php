<?php

namespace Magenest\Directory\Setup\Patch\Data;

use FontLib\Table\Type\name;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;


class UpdateMappingProvince implements DataPatchInterface
{
    protected $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magenest\ViettelPost\Helper\Data $helperData
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->helperData = $helperData;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
    public function stripVN($str) {
        $str = preg_replace("/(Định)/", 'Dinh', $str);
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/ -/", '', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        $str = preg_replace("/(Ð)/", 'D', $str);
        return $str;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();

        $table = $connection->getTableName('directory_city_entity');

        $addressData = $this->helperData->getAddressData($this->helperData->getProvinceUrlApi());
        foreach ($addressData as $address){
            $name = $this->stripVN($address['province_name']);
            $connection->update(
                $table,
                [
                    'province_code' => $address['province_code'],
                ],
                ['name LIKE ?' => '%'. $name .'%',]
            );
            $connection->update(
                $table,
                [
                    'province_code' => $address['province_code'],
                ],
                ['name = ?' => $address['province_name']]
            );
        }
        $this->moduleDataSetup->endSetup();


    }
}
