<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DataManipulators extends AbstractExtension
{
    public function getName() : string
    {
        return 'kirjastot_fi_date_filters';
    }

    public function getFilters() : array
    {
        return [
            new TwigFilter('filterByField', [$this, 'filterByField']),
            new TwigFilter('sortByField', [$this, 'sortByField']),
            new TwigFilter('sliceText', [$this, 'sliceText']),
            new TwigFilter('mailTo', [$this, 'obfuscateEmail']),
        ];
    }

    public function filterByField(array $data, string $field) : array
    {
        $empty = [null, false, ''];

        $filtered = array_filter($data, function($item) use($field, $empty) {
            if (is_object($item)) {
                return !in_array($item->{$field}, $empty, true);
            } elseif (is_array($item)) {
                return !in_array($item[$field], $empty, true);
            }
        });

        return $filtered;
    }

    public function sortByField(array $data, string $field) : array
    {
        usort($data, function($a, $b) use($field) {
            if (is_object($a)) {
                $a = $a->{$field} ?? null;
                $b = $b->{$field} ?? null;
            } elseif (is_array($a)) {
                $a = $a[$field] ?? null;
                $b = $b[$field] ?? null;
            }
            return strcasecmp($a, $b);
        });

        return $data;
    }

    public function sliceText(string $text, int $a, int $b = null) : string
    {
        $range_min = $a;
        $range_length = is_null($b) ? mb_strlen($text) : $b;
        $text = preg_replace('/\s+/', ' ', $text);

        $start = $range_min ? $this->findLastSpaceBefore($text, $range_min) : $range_min;
        $end = $this->findLastSpaceBefore(mb_substr($text, $range_min), $range_length);

        if ($start === false || $end === false) {
            return $text;
        } else {
            return mb_substr($text, $start, $end + $start);
        }

        return 'foo';
    }

    public function obfuscateEmail(?string $email) : ?string
    {
        if ($email) {
            return str_replace('@', '#', str_rot13($email));
        } else {
            return $email;
        }
    }

    private function findLastSpaceBefore(string $text, int $pos) : int
    {
        $text = mb_substr($text, 0, $pos);
        return strrpos($text, ' ');
    }
}
