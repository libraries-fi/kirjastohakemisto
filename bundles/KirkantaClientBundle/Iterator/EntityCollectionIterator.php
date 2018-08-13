<?php

namespace KirjastotFi\KirkantaClientBundle\Iterator;

use ArrayIterator;
use stdClass;

class EntityCollectionIterator extends ArrayIterator implements \JsonSerializable
{
    private $entity_class;
    private $references;

    public function __construct($entity_class, array $data, stdClass $refs = null)
    {
        parent::__construct($data);
        $this->entity_class = $entity_class;
        $this->references = $refs;
    }

    public function current()
    {
        return $this->wrap(parent::current());
    }

    public function first()
    {
        if ($this->count()) {
            return $this->wrap(current($this->getArrayCopy()));
        }
    }

    public function jsonSerialize() {
      return iterator_to_array($this);
    }

    protected function wrap(stdClass $object = null)
    {
        if ($object) {
            $class = $this->entity_class;
            return new $class($object, $this->references);
        }
    }
}
