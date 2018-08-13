<?php

namespace KirjastotFi\KirkantaClientBundle\Helper;

use ArrayIterator;
use IteratorAggregate;
use stdClass;

class WebLinks implements IteratorAggregate
{
    private $links;
    private $cache;

    private $known = [
        'facebook' => 'facebook.com',
        'flickr' => 'flickr.com',
        'googleplus' => 'plus.google.com',
        'instagram' => 'instagram.com',
        'pinterest' => 'pinterest.com',
        'twitter' => 'twitter.com',
        'vimeo' => 'vimeo.com',
        'youtube' => 'youtube.com',
    ];

    public function __construct(array $links)
    {
        $this->links = $links;
        $this->cache = (object)[
            'some' => null,
        ];
    }

    /**
     * Returns known social media links.
     */
    public function some()
    {
        if (is_null($this->cache->some)) {
            $some = [];
            foreach ($this as $link) {
                $id = $this->domain($link->url);
                if (in_array($id, $this->known)) {
                    $link->id = strstr($id, '.', true);
                    $some[$link->id] = $link;
                }
            }
            $order = array_keys($this->known);
            usort($some, function($a, $b) use ($order) {
                return array_search($a->id, $order) - array_search($b->id, $order);
            });

            $this->cache->some = $some;
        }
        return $this->cache->some;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->links);
    }

    private function domain($url)
    {
        $domain = parse_url($url, PHP_URL_HOST);
        return strpos($domain, 'www.') === 0 ? substr($domain, 4) : $domain;
    }
}
