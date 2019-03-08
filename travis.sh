set -e

if [ "$TEST_SUITE" = "marketplaceeqp" ];
then
    composer global config http-basic.repo.magento.com $MAGENTO_USERNAME $MAGENTO_PASSWORD
    composer require --prefer-source --no-interaction squizlabs/php_codesniffer magento/marketplace-eqp
    vendor/squizlabs/php_codesniffer/scripts/phpcs --config-set installed_paths vendor/magento/marketplace-eqp
    vendor/squizlabs/php_codesniffer/scripts/phpcs -n --standard="MEQP2" ./;
fi
