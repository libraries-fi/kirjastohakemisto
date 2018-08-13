<?php

namespace KirjastotFi\KirkantaClientBundle\Helper;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use stdClass;

class Services implements ArrayAccess, IteratorAggregate
{
    private $services;
    private $groups;

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    public function offsetGet($group_id)
    {
        $groups = $this->grouped();
        return $this->groups[$group_id];
    }

    public function offsetExists($group_id)
    {
        return array_key_exists($group_id, $this->grouped());
    }

    public function offsetSet($key, $value)
    {
        throw new \Exception('Read only');
    }

    public function offsetUnset($key)
    {
        throw new \Exception('Read only');
    }

    public function getIterator()
    {
        return new ArrayIterator($this->services);
    }

    public function groups()
    {
        return array_keys($this->grouped());
    }

    public function grouped()
    {
        if (!$this->groups) {
            $groups = [];
            foreach ($this as $service) {
                $groups[$service->type][] = $service;
            }
            $this->groups = $groups;
        }
        return $this->groups;
    }
}
