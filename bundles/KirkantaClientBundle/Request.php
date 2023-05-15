<?php

namespace KirjastotFi\KirkantaClientBundle;

/**
 * @deprecated
 */
class Request
{
    private $agent = 'Kirjastohakemisto/1.0';
    private $domain;
    private $version = 'v3';
    private $resource;
    private $query;

    public $limit;
    public $skip;

    public function __construct($resource, array $query = [], $limit = 50, $skip = 0)
    {
        exit(__CLASS__ . ' is deprecated');

        $this->domain = getenv('API_DOMAIN');
        $this->resource = $resource;
        $this->query = $query;
        $this->limit = max((int)$limit, 1);
        $this->skip = max((int)$skip, 0);
    }

    public function get()
    {
        $response = file_get_contents($this->url());
        return json_decode($response);
    }

    private function url()
    {
        $query = $this->queryString();
        return sprintf('https://%s/%s/%s?%s', $this->domain, $this->version, $this->resource, $query);
    }

    private function queryString()
    {
        $query = array_merge($this->query, [
            'limit' => $this->limit,
            'skip' => $this->skip,
        ]);

        if (isset($query['sort']) && is_array($query['sort'])) {
            foreach ($query['sort'] as $field => $dir) {
                $value = sprintf('%s%s', strtolower($dir) == 'desc' ? '-' : '', $field);
                $query['sort'][$field] = $value;
            }
            $query['sort'] = array_values($query['sort']);
        }

        foreach ($query as $key => $value) {
            if (is_array($value)) {
                $query[$key] = implode(',', $value);
            }
        }
        return http_build_query($query);
    }
}
