<?php

namespace KirjastotFi\KirkantaClientBundle\Entity;

class Picture extends Entity
{
    public function small()
    {
        return $this->raw->files->small;
    }

    public function medium()
    {
        return $this->raw->files->medium;
    }

    public function large()
    {
        return $this->raw->files->large;
    }

    public function huge()
    {
        return $this->raw->files->huge;
    }

    public function description()
    {
        return $this->raw->description;
    }
}
