<?php

namespace KirjastotFi\KirkantaClientBundle;

class Client
{
    private $agent;
    private $domain;
    private $version = 'v3';

    public function __construct(string $agent, string $domain)
    {
        $this->agent = $agent;
        $this->domain = $domain;
    }

    public function get(string $resource, array $query = [], $limit = 50, $skip = 0)
    {
        if ($limit > 0) {
            $query['limit'] = max((int)$limit, 1);
            $query['skip'] = max((int)$skip, 0);
        } else {
            $query['limit'] = 9999;
        }

        $response = file_get_contents($this->url($resource, $query));

        if (!$response) {
            throw new \RuntimeException('Could not connect to Kirkanta API');
        }
        return json_decode($response);
    }

    private function url($resource, array $query)
    {
        $query = $this->queryString($query);
        $url = sprintf('%s/%s/%s?%s', $this->domain, $this->version, $resource, $query);
        return $url;
    }

    private function queryString(array $query)
    {
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
