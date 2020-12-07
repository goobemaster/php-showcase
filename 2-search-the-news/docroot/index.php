<?php

    include_once('../Feeder.php');

    $feeder = Feeder::getInstance();

    if (isset($_POST['qt'])) {
        echo json_encode($feeder->getTypeAheadResults($_POST['qt']));
        exit(0);
    }
    if (isset($_GET['q'])) {
        $results = $feeder->getResults($_GET['q']);
    }

?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <title>Search The News</title>
    <meta name="description" content="Your very own private news search engine. KISS solutions to blatantly easy tasks with the right ratio of server and client side code.">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="og:title" content="Search The News">
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
        <div class="col center">
            <img src="resources/rss-logo.png">
            <h1>Search the News!</h1>
        </div>
    </header>

    <nav class="container">
        <div class="col">
            <input type="text" placeholder="Enter a topic or keyword..."<?php if (!empty($results)) echo ' value="' . $_GET['q'] . '"'; ?>>
            <button><img src="resources/search-icon.png"></button>
        </div>
    </nav>

    <main class="container">
        <div class="col">
        <?php if (!empty($results)) {
            foreach ($results as $guid => $article) {
                $date = is_int($article->published) ? (new DateTime('@' . $article->published))->format(DateTimeInterface::RSS) : '';
                ?>
                <p><strong class="orange">◉</strong> <a href="<?= $article->link ?>" target="_blank"><?= $article->title ?></a><br>
                <small><?= $article->link ?></small><small><?= $date ?></small>
                <section><?= $article->description ?></section>
                </p>
        <?php }} ?>
        </div>
    </main>

    <footer>
        <div class="decor"></div>
        <div class="container">
            <div class="col center">
                <p>"Search The News" Copyright © <?= (new DateTime())->format('Y'); ?> <a href="https://twitter.com/goobemaster">Gabor Major</a></p>
            </div>
            <div class="col center">
                <p>Part of the <a href="https://github.com/goobemaster/php-showcase">PHP-Showcase</a> repository.</p>
            </div>
        </div>
        <p class="center">This program is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation, either version 3 of the License, or
        (at your option) any later version.</p>
    </footer>

    <script src="resources/main.js"></script>

</body>

</html>