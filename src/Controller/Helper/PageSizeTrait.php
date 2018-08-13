<?php

namespace App\Controller\Helper;

/**
 * Helper for fetching page size limit for API calls inside controllers.
 */
trait PageSizeTrait
{
    public function pageSize()
    {
        return $this->getParameter('kirkanta_result_size');
    }
}
