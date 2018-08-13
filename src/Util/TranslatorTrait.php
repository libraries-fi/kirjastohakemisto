<?php

namespace App\Util;

trait TranslatorTrait
{
    protected $translator;

    public function tr($string)
    {
        return $this->translator->trans($string);
    }
}
