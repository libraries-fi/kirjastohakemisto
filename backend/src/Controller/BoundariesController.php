<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\ApcuCache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BoundariesController extends Controller
{
    const BOUNDARY_DATA_DIR = __DIR__ . '/../../../osm';
    const LANGCODES = ['fi', 'en', 'sv'];

    private $cache;

    public function __construct()
    {
        $this->cache = new ApcuCache('regions', 1800);
        $this->buildFileCache();
    }

    /**
     * Returns geographical boundaries for requested municipalities separately.
     *
     * Keys in returned object are the same as in input parameter except that keys that don't
     * match to a region will be discarded.
     */
    public function regions(Request $request)
    {
        $langcode = $request->query->get('lang', 'fi');
        $cities = explode(' ', $request->query->get('q'));
        $data = $this->getCityBoundaries($cities, $langcode);
        return new JsonResponse($data);
    }

    private function getCityBoundaries(array $cities, string $langcode) : array
    {
        $data = [];

        foreach ($cities as $city) {
            $cacheKey = $this->cache->get(mb_strtolower("{$city}.{$langcode}"));

            if ($cacheKey) {
                // Return key using same KeyCase as used in the request.
                $data[$city] = $this->cache->get($cacheKey);
            } elseif ($fallback = $this->cache->get(mb_strtolower($city))) {
                // Sometimes Kirkanta has the wrong name for a translation.
                $data[$city] = $fallback;
            }
        }

        return $data;
    }

    private function buildFileCache() : void
    {
        $files = glob(self::BOUNDARY_DATA_DIR . '/*.GeoJson');
        $initKey = '_init';

        if (!$this->cache->has($initKey)) {
            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file));
                $tags = $data->properties->alltags;
                $standardName = mb_strtolower(str_replace(':', '', $tags->name));
                $this->cache->set($standardName, $this->flipCoordinates($data->geometry->coordinates[0]));

                foreach (self::LANGCODES as $langcode) {
                    $mappedName = mb_strtolower($tags->{"name:{$langcode}"} ?? $standardName);
                    $mappedName = str_replace(':', '', $mappedName);
                    $this->cache->set("{$mappedName}.{$langcode}", $standardName);
                }
            }

            $this->cache->set($initKey, true);
        }
    }

    private function flipCoordinates(array $data) : array
    {
        foreach ($data as $i => $sub) {
            if (is_array($sub[0])) {
                $data[$i] = $this->flipCoordinates($sub);
            } else {
                $data[$i] = [$sub[1], $sub[0]];
            }
        }

        return $data;
    }

    private function flattenArray(array $data) : array
    {
        if (!is_array($data[0][0])) {
            return $data;
        }

        $flat = [];

        foreach ($data as $sub) {
            $flat = array_merge($flat, $this->flattenArray($sub));
        }

        return $flat;
    }
}
