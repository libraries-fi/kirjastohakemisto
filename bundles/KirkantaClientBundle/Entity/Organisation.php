<?php

namespace KirjastotFi\KirkantaClientBundle\Entity;

use stdClass;
use ArrayIterator;
use KirjastotFi\KirkantaClientBundle\Helper\Services;
use KirjastotFi\KirkantaClientBundle\Helper\WebLinks;
use KirjastotFi\KirkantaClientBundle\Iterator\EntityCollectionIterator;

class Organisation extends Entity
{
    private $weblinks;

    public static function formatPhoneNumber(string $number) : string
    {
        $regions = ['02', '03', '05', '06', '08', '09', '013', '014', '015', '016', '017', '018', '019'];
        $operators = ['040', '041', '042', '043', '044', '045', '046', '049', '050'];
        $extra = ['0100', '0200', '0700', '0800'];
        $input = preg_replace('/[\s\(\)]/', '', $number);
        $output = '';

      	if (!ctype_digit($input)) {
      	    return $number;
        }

        foreach (array_merge($operators, $regions, $extra) as $prefix) {
            if (strpos($input, $prefix) === 0) {
                $output = substr($input, 0, strlen($prefix));
                $input = substr($input, strlen($prefix));
                break;
            }
        }

        $mid = floor(strlen($input) / 2);
        $output = sprintf('%s %s %s', $output, substr($input, 0, $mid), substr($input, $mid));

        return trim($output);
    }

    public function __construct(stdClass $raw = null, stdClass $refs = null)
    {
        parent::__construct($raw, $refs);

        if (!empty($this->raw->schedules)) {
            foreach ($this->raw->schedules as $i => $day) {
                $this->raw->schedules[$i] = ScheduleDay::toV4Format($day);
            }
        }

        if (!empty($this->raw->services)) {
            $instances = [];

            usort($this->raw->services, function($a, $b) {
                if ($x = strcasecmp($a->type, $b->type)) {
                    return $x;
                }

                return strcasecmp($a->name, $b->name);
            });

            /*
             * Do this after sorting so that ID does not change
             * after order changes in source array.
             */
            foreach ($this->raw->services as $s) {
                if (!isset($instances[$s->id])) {
                    $instances[$s->id] = 1;
                } else {
                    $instances[$s->id]++;
                }

                if (($i = $instances[$s->id]) > 1) {
                    $s->unique_id = sprintf('%s-%d', $s->slug, $i);
                } else {
                    $s->unique_id = $s->slug;
                }
            }
        }

        if (!empty($this->raw->phone_numbers)) {
            foreach ($this->raw->phone_numbers as $entry) {
                if (!empty($entry->number)) {
                    $entry->number = self::formatPhoneNumber($entry->number);
                }
            }
        }

        if (!empty($this->raw->persons)) {
            foreach ($this->raw->persons as $entry) {
                if (!empty($entry->phone)) {
                    $entry->phone = self::formatPhoneNumber($entry->phone);
                }
            }

            usort($this->raw->persons, function($a, $b) {
              return strcasecmp($a->last_name, $b->last_name);
            });
        }
    }

    public function id()
    {
        return $this->raw->id;
    }

    public function slug()
    {
        return $this->raw->slug;
    }

    public function name()
    {
        return $this->raw->name;
    }

    public function services()
    {
        foreach ($this->raw->services as $key => $service) {
            $this->raw->services[$key]->description = 
                \HTMLPurifier::getInstance()->purify($service->description);
        }
        return new Services($this->raw->services);
    }

    // public function pictures()
    // {
    //     return new EntityCollectionIterator(Picture::class, $this->raw->pictures);
    // }

    public function phoneNumbers()
    {
        return new ArrayIterator($this->raw->phone_numbers);
    }

    public function schedules()
    {
        return new EntityCollectionIterator(ScheduleDay::class, $this->raw->schedules);
    }

    public function city()
    {
        return $this->ref('city', $this->raw->city, City::class);
    }

    public function consortium()
    {
        return $this->ref('consortium', $this->raw->consortium, Consortium::class);
    }

    public function address()
    {
        return $this->raw->address;
    }

    public function coordinates()
    {
        if ($this->address()) {
            if ($coords = $this->address()->coordinates) {
                return sprintf('%2.8f,%2.8f', $coords->lat, $coords->lon);
            }
        }
    }

    public function mailAddress()
    {
        return $this->raw->mail_address;
    }

    protected function extra()
    {
        return $this->raw->extra;
    }

    public function slogan()
    {
        return $this->extra()->slogan;
    }

    public function description()
    {
        return $this->extra()->description;
    }

    public function transit() {
        return $this->extra()->transit;
    }

    public function links() {
        if (!$this->weblinks) {
            $this->weblinks = new WebLinks($this->raw->links);
        }
        return $this->weblinks;
    }
}
