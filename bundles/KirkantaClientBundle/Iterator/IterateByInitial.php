<?php

namespace KirjastotFi\KirkantaClientBundle\Iterator;

use ArrayIterator;
use IteratorAggregate;

class IterateByInitial implements IteratorAggregate
{
    private $cache;
    private $data;
    private $key;

    public function __construct($data, $index_by)
    {
        $this->data = $data;
        $this->key = $index_by;
    }

    public function getIterator()
    {
        if (is_null($this->cache)) {
            $this->build();
        }

        return new ArrayIterator($this->cache);
    }

    private function build()
    {
        foreach ($this->data as $i => $item) {
            $value = strtoupper(call_user_func([$item, $this->key]));
            $key = mb_substr($value, 0, 1);
            $this->cache[$key][$value . $i] = $item;
        }

        foreach ($this->cache as &$sub) {
            ksort($sub);
        }

        ksort($this->cache);
    }
}
