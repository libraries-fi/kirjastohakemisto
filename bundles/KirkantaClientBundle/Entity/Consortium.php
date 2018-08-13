<?php

namespace KirjastotFi\KirkantaClientBundle\Entity;

class Consortium extends Entity
{
    public function id() {
        return $this->raw->id;
    }

    public function name() {
        return $this->raw->name;
    }

    public function isSpecial()
    {
        return $this->raw->special;
    }
}
