<?php

namespace KirjastotFi\KirkantaClientBundle;

class DocumentType
{
    private $id;
    private $class;
    private $repository_class;

    public function __construct($type_id, $class_name)
    {
        $this->id = $type_id;
        $this->class = $class_name;
        $this->repository_class = Repository\DefaultRepository::class;
    }

    public function getTypeId()
    {
        return $this->id;
    }

    public function getClassName()
    {
        return $this->class;
    }

    public function getRepositoryClass()
    {
        return $this->repository_class;
    }
}
