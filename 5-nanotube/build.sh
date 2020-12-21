#!/bin/bash

cd common
composer install
php -d phar.readonly=off vendor/clue/phar-composer/bin/phar-composer build .
cd ..

cd auth
composer install
cd ..
rm auth/common.phar
cp common/common.phar auth/common.phar

cd connection
composer install
cd ..
rm connection/common.phar
cp common/common.phar connection/common.phar

cd content
composer install
cd ..
rm content/common.phar
cp common/common.phar content/common.phar

cd webui
composer install
cd ..
rm webui/common.phar
cp common/common.phar webui/common.phar

rm common/common.phar