<?php

namespace KirjastotFi\KirkantaClientBundle\Entity;

class City extends Entity
{
    public function id() {
        return $this->raw->id;
    }

    public function name() {
        return $this->raw->name;
    }
}
