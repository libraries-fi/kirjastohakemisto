<?php

namespace KirjastotFi\KirkantaClientBundle\Entity;

use stdClass;
use JsonSerializable;

abstract class Entity implements JsonSerializable
{
    protected $raw;
    public $refs;

    public function __construct(stdClass $raw = null, stdClass $refs = null)
    {
        $this->raw = $raw;
        $this->refs = $refs;

        if ($refs) {
            // $this->refs = new Refs($refs);
        }
    }

    public function __call($field, $arguments)
    {
        return $this->raw->{$field} ?? null;
    }

    public function __get($field) {
        return $this->raw->{$field} ?? null;
    }

    public function jsonSerialize()
    {
        $values = [];

        foreach ($this->raw as $field => $data) {
            if (method_exists($this, $field)) {
                $values[$field] = call_user_func([$this, $field]);
            } else {
                $values[$field] = $data;
            }
        }

        return $values;
    }

    protected function ref($type, $id, $class = null)
    {
        if (isset($this->refs->{$type}->{$id})) {
            $data = $this->refs->{$type}->{$id};
            return $class ? new $class($data) : $data;
        }
    }
}
