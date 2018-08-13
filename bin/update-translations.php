#!/usr/bin/php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$DIR = __DIR__ . '/../translations';
$BASE_LOCALE = 'fi';
$BASE_DATA = [];
$IGNORE_FILES = ['messages.en.yaml'];

$files = glob(sprintf('%s/*.yaml', $DIR));

usort($files, function($a, $b) use($BASE_LOCALE) {
  $a = strpos(basename($a), sprintf('.%s.', $BASE_LOCALE)) !== false;
  $b = strpos(basename($b), sprintf('.%s.', $BASE_LOCALE)) !== false;

  if ($a ^ $b) {
    return $a ? -1 : 1;
  } else {
    return 0;
  }
});

foreach ($files as $file) {
  $name = basename($file, '.yaml');
  $key = substr($name, 0, -3);
  $data = Yaml::parseFile($file);

  if (in_array(basename($file), $IGNORE_FILES)) {
    continue;
  }

  if (substr($name, -3) == '.' . $BASE_LOCALE) {
    array_walk($data, 'clear_translation');

    $BASE_DATA[$key] = $data;
  } else {
    $data = sort_translation($data);
    $data = merge_translations($data, $BASE_DATA[$key]);
    file_put_contents($file, Yaml::dump($data, 4, 4, Yaml::DUMP_OBJECT | YAML::DUMP_OBJECT_AS_MAP ));
  }
}

function clear_translation(&$value) {
  if (is_array($value)) {
    array_walk($value, 'clear_translation');
  } else {
    $value = '';
  }
}

function merge_translations(array &$translation, array $base_data) : array {
  /*
   * Filter previously merged strings that haven't been translated yet.
   * This way they will be pushed to the end of the file again.
   */
  $translation = array_filter($translation, function($value) {
    return is_array($value) || strlen($value) > 0;
  });

  foreach ($base_data as $key => $value) {
    if (!isset($translation[$key])) {
      $translation[$key] = $value;
    } elseif (is_array($value)) {
      $translation[$key] = merge_translations($translation[$key], $value);
    }
  }

  return $translation;
}

function sort_translation(array &$translation) : array {
  ksort($translation, SORT_STRING);

  foreach ($translation as &$value) {
    if (is_array($value)) {
      sort_translation($value);
    }
  }

  return $translation;
}
