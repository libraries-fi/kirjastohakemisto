<?php

namespace App\Controller\Helper;

trait RepositoryAccessTrait
{
    protected function getRepository($type_id)
    {
        return $this->get('kirjastotfi.hakemisto_api.manager')->getRepository($type_id);
    }
}
