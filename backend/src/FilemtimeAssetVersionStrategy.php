<?php

namespace App;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class FilemtimeAssetVersionStrategy implements VersionStrategyInterface
{
    public function getVersion($path)
    {
        $webroot = $_SERVER['DOCUMENT_ROOT'];
        $filepath = realpath("{$webroot}/{$path}");
        return filemtime($filepath);
    }

    public function applyVersion($path)
    {
        return sprintf('%s?v=%s', $path, $this->getVersion($path));
    }
}
