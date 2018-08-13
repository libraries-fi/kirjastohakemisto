<?php

namespace KirjastotFi\KirkantaClientBundle\Repository;

use stdClass;
use KirjastotFi\KirkantaClientBundle\Client;
use KirjastotFi\KirkantaClientBundle\Request;
use KirjastotFi\KirkantaClientBundle\Result;

/**
 * Base implementation of repositories used to query the API.
 *
 * The API imitates that of Doctrine's ObjectRepositories for convenience.
 */
class DefaultRepository
{
    private $cache;
    private $client;
    private $metadata;

    public function __construct(Client $client, $metadata)
    {
        $this->client = $client;
        $this->metadata = $metadata;
    }

    public function findBy(array $filters, array $sort = null, $limit = null, $skip = null)
    {
        $filters = ['sort' => $sort] + $filters;
        $response = $this->request($this->metadata->getTypeId(), $filters, $limit, $skip);

        if ($response) {
          $result = $this->result($this->metadata->getClassName(), $response);
          return $result;
      } else {
          return new Result($this->metadata->getClassName(), []);
      }
    }

    public function findOneBy(array $filters, array $sort = null)
    {
        foreach ($this->findBy($filters, $sort, 1) as $result) {
            return $result;
        }
    }

    protected function request($resource, array $filters, $limit, $skip)
    {
        return $this->client->get($resource, $filters, $limit, $skip);
    }

    protected function result($class_name, stdClass $response)
    {
        return new Result($class_name, $response);
    }
}
