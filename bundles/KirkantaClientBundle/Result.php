<?php

namespace KirjastotFi\KirkantaClientBundle;

use stdClass;
use Countable;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use KirjastotFi\KirkantaClientBundle\Iterator\EntityCollectionIterator;

class Result implements Countable, IteratorAggregate, JsonSerializable
{
    public $total;
    public $count;
    public $items;
    public $refs;

    private $raw;
    private $entity_class;
    private $data;

    public function __construct($entity_class, $data)
    {
        $this->entity_class = $entity_class;
        $this->raw = $data;
        $this->refs = isset($this->raw->references) ? $this->raw->references : new stdClass;
        $this->total = $this->raw->total;
        $this->data = $this->raw->items;
        $this->items = $this->getIterator();
        $this->count = count($this->data);
    }

    public function count() : int
    {
        return count($this->data);
    }

    public function getIterator() : Iterator
    {
        return new EntityCollectionIterator($this->entity_class, $this->data, $this->refs);
    }

    public function jsonSerialize()
    {
        $data = [
            'total' => $this->total,
            'count' => $this->count,
            'refs' => $this->refs,
            'items' => iterator_to_array($this->getIterator()),
        ];

        return $data;
    }
}
