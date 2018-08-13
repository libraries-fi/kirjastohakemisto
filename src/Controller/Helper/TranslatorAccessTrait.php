<?php

namespace App\Controller\Helper;

trait TranslatorAccessTrait
{
    protected function tr($message)
    {
        return $this->get('translator')->trans($message);
    }
}
