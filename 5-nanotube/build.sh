#!/bin/bash

cd common
composer install
php -d phar.readonly=off vendor/clue/phar-composer/bin/phar-composer build .
cd ..

declare -a services=("auth" "connection" "content" "webui")

for i in "${services[@]}"
do
    cd $i
    composer install
    cd ..
    rm $i/common.phar
    rm $i/routes.ser
    cp common/common.phar $i/common.phar
done

rm common/common.phar