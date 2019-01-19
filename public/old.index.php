<?php

function webapp_script() {
  $paths = [
    'dev/main.js',
    'dist/main.js',
  ];

  foreach ($paths as $path) {
    if (is_file($path)) {
      $mtime = filemtime($path);
      return "/{$path}?{$mtime}";
    }
  }

  exit('Failed to locate the script file.');
}

?>
<!DOCTYPE html>
<html lang="fi">
  <head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Suomen kirjastot – Kirjastohakemisto</title>
    <style>
      @import url("https://fonts.googleapis.com/css?family=Roboto+Condensed:400,700|Roboto:300,300i,400,700");
    </style>
  </head>
  <body id="page--frontpage">
    <div id="app"></div>
    <noscript>
      <header>
        <h1>Finnish Library Directory</h1>
        <p>Welcome to the Finnish Library Directory. Here you will find all the libraries in Finland.</p>
        <p>For the best experience you should enable support for JavaScript in your browser.</p>
        <p>To ensure compatibility with less capable browsers, we do also offer a primitive user interface for accessing the information on this website.</p>
      </header>

      <h2>Table of Contents</h2>
      <ul>
        <li><a href="/search">Search</a></li>
        <li><a href="/libraries">Libraries</a></li>
        <li><a href="/services">Services</a></li>
        <li><a href="/consortiums">Library consortiums</a></li>
      </ul>
    </noscript>
    <script src="<?= webapp_script() ?>"></script>
  </body>
</html>
