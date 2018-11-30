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
      Build here a simple interface for Links.
    </noscript>
    <script src="<?= webapp_script() ?>"></script>
  </body>
</html>
