<?php

namespace KirjastotFi\KirkantaClientBundle\Entity;

use ArrayIterator;
use KirjastotFi\KirkantaClientBundle\Helper\Services;
use KirjastotFi\KirkantaClientBundle\Helper\WebLinks;
use KirjastotFi\KirkantaClientBundle\Iterator\EntityCollectionIterator;

class Service extends Entity
{
    public function id()
    {
        return $this->raw->id;
    }

    public function slug()
    {
        return $this->raw->slug;
    }

    public function name()
    {
        return $this->raw->name;
    }

    public function type()
    {
        return $this->raw->type;
    }
}
