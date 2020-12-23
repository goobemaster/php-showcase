#!/bin/bash

declare -a services=("auth" "connection" "content" "webui" "common")

for i in "${services[@]}"
do
    cd $i
    rm common.phar
    rm routes.ser
    rm composer.lock
    rm -rf vendor
    cd ..
done