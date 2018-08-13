<?php

namespace KirjastotFi\KirkantaClientBundle;

use KirjastotFi\KirkantaClientBundle\Repository\DefaultRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DocumentTypeManager
{
    private $client;
    private $types;

    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->types = $this->toTypeCache([
            new DocumentType('organisation', Entity\Organisation::class),
            new DocumentType('consortium', Entity\Consortium::class),
            new DocumentType('library', Entity\Organisation::class),
            new DocumentType('person', Entity\Person::class),
            new DocumentType('service', Entity\Service::class),
            new DocumentType('city', Entity\City::class),
        ]);
    }

    public function getRepository($type)
    {
        $type = $this->types[$type];
        $class = $type->getRepositoryClass();
        return new $class($this->client, $type);
    }

    private function toTypeCache(array $types)
    {
        $cache = [];
        foreach ($types as $type) {
            $cache[$type->getTypeId()] = $type;
        }
        return $cache;
    }
}
