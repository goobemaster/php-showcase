<?php

    const ALLOWED_CONTENT = [
        'c' => 'Ansi C',
        'java' => 'Java',
        'py' => 'Python',
        'rb' => 'Ruby'
    ];

    $fetchFileName = '../content/' . $_GET['fetch'];
    if (isset($_GET['fetch'])) {
        if (!array_key_exists(pathinfo($fetchFileName, PATHINFO_EXTENSION), ALLOWED_CONTENT) ||
            !file_exists($fetchFileName)) {
            echo '<pre class="error">Sorry, the file you\'ve been looking for does not exist!</pre>';
            exit(0);
        }
        echo file_get_contents($fetchFileName);
        exit(0);
    }

?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <title>Code Show Off</title>
    <meta name="description" content="Show off your code snippets on a page. KISS solutions to blatantly easy tasks with the right ratio of server and client side code.">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="og:title" content="Code Show Off">
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

    <header class="container">
        <div class="col">
            <img src="favicon.png"><h1>Code Show Off</h1>
        </div>
    </header>

    <div class="container">
        <nav class="col">
            <ul>
            <?php foreach (ALLOWED_CONTENT as $extension => $language) {
                $contentFiles = glob(sprintf('../content/*.%s', $extension));
                if (empty($contentFiles)) continue; ?>
                <li><h2><?= $language ?></h2></li><ul>
                <?php foreach ($contentFiles as $filename) { echo sprintf('<li><a href="#%s">%s</a></li>', basename($filename), basename($filename)); } ?> 
                </ul>
            <?php } ?>
            </ul>
        </nav>

        <main class="col">         
        </main>
    </div>

    <footer>
        <div class="container">
            <div class="col center">
                <p>"Code Show Off" Copyright © <?= (new DateTime())->format('Y'); ?> <a href="https://twitter.com/goobemaster">Gabor Major</a></p>
            </div>
            <div class="col center">
                <p>Part of the <a href="https://github.com/goobemaster/php-showcase">PHP-Showcase</a> repository.</p>
            </div>
        </div>
        <p class="center">This program is free software: you can redistribute it and/or modify<br>
        it under the terms of the GNU General Public License as published by<br>
        the Free Software Foundation, either version 3 of the License, or<br>
        (at your option) any later version.</p>
    </footer>

    <script src="resources/main.js"></script>

</body>

</html>