<?php

require('phar://common.phar');
require_once('vendor/autoload.php');

$classLoader->addPrefixPath(__DIR__ . '/src', 'Nanotube\Content');
$classLoader->register();

$service = new Nanotube\Content\ContentService();