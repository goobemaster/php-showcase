<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <title>Restless Parrot</title>
    <meta name="description" content="Your very own mock server for development and testing without a working backend. KISS solutions to blatantly easy tasks with the right ratio of server and client side code.">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="og:title" content="Restless Parrot">
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

    <header><h1><span>⚙</span> Mock Server Configurator</h1></header>

    <main class="container">
        <nav class="col">
            <h2>
                <span>⬎</span> Resources 
                <button title="Register a new mock request" onclick="APP.show_new_request_form();"><span><strong>+</strong></span></button>
            </h2>
            <ul></ul>
        </nav>
        <form class="col" id="registered" onsubmit="return false;">
            <h2><span>⬑</span> Registered Resource</h2>
            <blockquote></blockquote>
            <section class="container">
                <button onclick="APP.delete_resource();">⏎ Delete Resource</button>
            </section>
        </form>
        <form class="col" id="resource" onsubmit="return false;">
            <h2><span>+</span> New Mock Resource...</h2>

            <section id="req">
                <h3>Request</h3>
                <label for="req_method">Method:</label>
                <select id="req_method" required>
                    <option value="Get">GET</option>
                    <option value="Post">POST</option>                
                    <option value="Put">PUT</option>                
                    <option value="Delete">DELETE</option>                
                </select>

                <label for="req_path">Path:</label>
                <input type="text" id="req_path" placeholder="/some/route?param=value" required>

                <label for="req_body">Body:</label>
                <textarea placeholder="{}" id="req_body" spellcheck="false"></textarea>

                <label>Headers:</label>
                <table id="req_headers">
                    <thead><tr><th>Name</th><th>Value</th><th>&nbsp;</th></tr></thead>
                    <tbody></tbody>
                </table>
                <small>Mandatory headers for mock requests: <strong>Host, Accept</strong> !</small>
            </section>

            <section id="res">
                <h3>Response</h3>
                <label for="res_code">Status Code:</label>
                <input type="number" id="res_code" placeholder="200" required>

                <label for="res_body">Body:</label>
                <textarea placeholder="{}" id="res_body" spellcheck="false"></textarea>

                <label>Headers:</label>
                <table id="res_headers">
                    <thead><tr><th>Name</th><th>Value</th><th>&nbsp;</th></tr></thead>
                    <tbody></tbody>
                </table>
            </section>

            <section class="container">
                <button onclick="APP.save_resource();">⏎ Save to Server</button>
                <button onclick="APP.hide_forms();">Cancel</button>
            </section>
        </form>
    </main>

    <footer>
        <img src="resources/bird-1293799-340.webp">
        <section>
            <div class="container">
                <div class="col">
                    <p><small>"Restless Parrot" Copyright © <?= (new DateTime())->format('Y'); ?> <a href="https://twitter.com/goobemaster">Gabor Major</a></small></p>
                </div>
                <div class="col">
                    <p><small>Part of the <a href="https://github.com/goobemaster/php-showcase">PHP-Showcase</a> repository.</small></p>
                </div>
            </div>
        </section>
        <small>This program is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation, either version 3 of the License, or
        (at your option) any later version.</small>
    </footer>

    <script src="resources/main.js"></script>

</body>

</html>