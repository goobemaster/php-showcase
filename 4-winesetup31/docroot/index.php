<?php

    require_once('../Installer.php');

    $installer = Installer::getInstance();
    if (isset($_GET['callback']) && $installer->callbackExists($_GET['callback'])) {
        $body = json_decode(file_get_contents('php://input'));
        if ($body !== null) $installer->runCallback($body);
    }

    // The following is just for demonstration purposes, you can delete it
    if (isset($_GET['login'])) {
        echo "<!doctype html><html class=\"no-js\" lang=\"en\"><body><p>The app has been installed at this point, and we've been redirected to the login page by the last \"DoneRedirect\" callback...</p></body></html>";
        exit(0);
    }

?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <title>Wine Setup 3.1</title>
    <meta name="description" content="Lorem Ipsum. KISS solutions to blatantly easy tasks with the right ratio of server and client side code.">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="og:title" content="Wine Setup 3.1">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://localhost:8000/">
    <meta property="og:image" content="favicon.png">

    <link rel="manifest" href="site.webmanifest">
    <link rel="apple-touch-icon" href="favicon.png">

    <link rel="stylesheet" href="https://pagecdn.io/lib/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="resources/style.css">

    <meta name="theme-color" content="#fafafa">
</head>

<body>
    <header>
        <h1><small>💾</small> <strong></strong> Setup</h1>
        <nav><ul></ul></nav>
    </header>

    <main class="window" id="setup">
        <section id="title"><h2></h2><button onclick="APP.show_app_info();">i</button></section>
        <section id="contents"></section>
    </main>

    <footer>
        <p>"Wine Setup 3.1" Copyright © <?= (new DateTime())->format('Y'); ?> <a href="https://twitter.com/goobemaster">Gabor Major</a></p>
        <p>Part of the <a href="https://github.com/goobemaster/php-showcase">PHP-Showcase</a> repository.</p>
        <p><small>This program is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation, either version 3 of the License, or
        (at your option) any later version.</small></p>
    </footer>

    <main class="window" id="alert">
        <section id="title"><h2></h2><button onclick="APP.close_alert();">ᳵ</button></section>
        <section id="contents"><p></p></section>
        <div class="buttons">
            <button onclick="APP.close_alert();">OK</button>
        </div>
    </main>

    <script src="resources/main.js"></script>
    <script>
        <?php $installer->initClient(); ?>
        APP.init();
    </script>
    <?php $installer->loadTheme(); ?>
</body>

</html>