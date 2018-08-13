<?php

namespace KirjastotFi\KirkantaClientBundle\Entity;

use ArrayIterator;
use KirjastotFi\KirkantaClientBundle\Helper\Services;
use KirjastotFi\KirkantaClientBundle\Helper\WebLinks;
use KirjastotFi\KirkantaClientBundle\Iterator\EntityCollectionIterator;

class Person extends Entity
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
        return sprintf('%s %s', $this->firstName(), $this->lastName());
    }

    public function firstName()
    {
        return $this->raw->first_name;
    }

    public function lastName()
    {
        return $this->raw->last_name;
    }
}
