<?php

namespace App\Dev;

use ArrayObject;

class Services extends ArrayObject
{
    public function __construct()
    {
        $data = json_decode(file_get_contents('../src/Resources/data/services.json'), true);
        parent::__construct($data);
    }
}
