<?php

require('phar://common.phar');
require_once('vendor/autoload.php');

$classLoader->addPrefixPath(__DIR__ . '/src', 'Nanotube\Webui');
$classLoader->register();

try {
    $service = new Nanotube\Webui\WebApi();
} catch (Exception $e) {
    // Not a valid API call. Nothing to do.
}
if (ob_get_length() > 0) exit();

?>

<!doctype html>
<html class="no-js" lang="en">

    <head>
        <meta charset="utf-8">
        <title>nanoTube</title>
        <meta name="description" content="nanoTube. KISS solutions to blatantly easy tasks with the right ratio of server and client side code.">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta property="og:title" content="nanoTube">
        <meta property="og:type" content="website">
        <meta property="og:url" content="http://localhost:8000/">
        <meta property="og:image" content="favicon.png">

        <link rel="apple-touch-icon" sizes="180x180" href="/resources/images/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/resources/images/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/resources/images/favicon/favicon-16x16.png">
        <link rel="manifest" href="/resources/images/favicon/site.webmanifest">
        <link rel="mask-icon" href="/resources/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <link rel="shortcut icon" href="/resources/images/favicon/favicon.ico">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="msapplication-config" content="/resources/images/favicon/browserconfig.xml">
        <meta name="theme-color" content="#ffffff">

        <script src="vendor/components/jquery/jquery.min.js"></script>
        <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
        <script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="vendor/fortawesome/font-awesome/css/all.min.css">
        <link rel="stylesheet" href="resources/style.css">
    </head>

    <body>
        <script src="resources/main.js"></script>
    </body>

</html>