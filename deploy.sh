#!/bin/bash

echo "=========== Enable maintenance mode ==========="
php7.4 bin/magento maintenance:enable

echo "=========== Running: git pull ==========="
git pull origin staging

echo "=========== Running: rm -rf var/generation/* var/di/* var/page_cache/* var/view_preprocessed/* var/cache/* var/composer_home/*  ==========="
rm -rf var/generation/* var/di/* var/page_cache/* var/view_preprocessed/* var/cache/* var/composer_home/*

echo "=========== Running: rm -rf pub/static/* ==========="
rm -rf pub/static/*

echo "=========== Running: php bin/magento setup:upgrade ==========="
php7.4 bin/magento setup:upgrade

echo "=========== Running: php bin/magento deploy:mode:set production -s ==========="
php7.4 bin/magento deploy:mode:set production -s

echo "=========== Running: php bin/magento setup:di:compile ==========="
php7.4 -d memory_limit=2048M bin/magento setup:di:compile

echo "=========== Running: php bin/magento setup:static-content:deploy ==========="
php7.4 bin/magento setup:static-content:deploy -f -j 4 vi_VN en_US

#echo "=========== Running: php bin/magento indexer:reindex ==========="
#php bin/magento indexer:reindex

echo "=========== Running: php bin/magento cache:clean ==========="
php7.4 bin/magento cache:clean

echo "=========== Disable maintenance mode ==========="
php7.4 bin/magento maintenance:disable

