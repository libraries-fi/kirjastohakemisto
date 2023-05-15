<?php

$data = file_get_contents(getenv('KIRKANTA_URL') . '/v3/service?limit=1000&sort=name&lang=fi');
$services = [];

foreach (json_decode($data)->items as $row) {
  $services[$row->name] = $row->id;
}

file_put_contents('../src/Resources/data/services.json', json_encode($services));

