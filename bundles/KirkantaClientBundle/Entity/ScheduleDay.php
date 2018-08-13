<?php

namespace KirjastotFi\KirkantaClientBundle\Entity;

use stdClass;

function swap(&$a, &$b) {
    $tmp = $a;
    $a = $b;
    $b = $tmp;
}

class ScheduleDay extends Entity
{
    public static function toV4Format(stdClass $day)
    {
        $times = [];

        foreach ($day->times as $time) {
            $time->staff = true;
            $time->status = 1;
            $times[] = $time;
        }

        if (!empty($day->sections->selfservice->times)) {
            foreach ($day->sections->selfservice->times as $time) {
                $time->staff = false;
                $time->status = 2;
                $times[] = $time;
            }

            usort($times, function($a, $b) {
                if ($x = strcmp($a->opens, $b->opens)) {
                    return $x;
                }
                return strcmp($a->closes, $b->closes);
            });

            for ($i = 0; $i < count($times); $i++) {
                if ($i == 0) {
                    continue;
                }

                $time = $times[$i];
                $prev = $times[$i - 1];

                if ($prev->opens == $time->opens) {
                    $time->opens = $prev->closes;
                }

                if ($prev->closes > $time->opens) {
                    $tmp = $prev->closes;
                    $prev->closes = $time->opens;

                    if ($tmp > $time->closes) {
                        $extra = (object)[
                            'opens' => $time->closes,
                            'closes' => $tmp,
                            'staff' => $prev->staff,
                            'status' => $prev->status,
                        ];

                        array_splice($times, $i, 1, [$time, $extra]);
                    }

                    if ($prev->opens == $prev->closes) {
                        unset($times[$i - 1]);
                    }

                    if ($time->opens > $time->closes) {
                        // swap($time->opens, $time->closes);
                    }
                }
            }
        }

        for ($i = 0; $i < count($times); $i++) {
            if ($i == 0) {
                continue;
            }

            $time = $times[$i];
            $prev = $times[$i - 1];

            if ($prev->closes < $time->opens) {
                $closed = (object)[
                    'opens' => $prev->closes,
                    'closes' => $time->opens,
                    'closed' => true,
                    'status' => 0,
                ];

                array_splice($times, $i, 1, [$closed, $time]);
            }
        }

        $day->status = null;
        $day->times = $times;

        if (empty($times)) {
            $day->closed = true;
        } else {
            $day->opens = reset($times)->opens;
            $day->closes = end($times)->closes;
            $day->closed = false;
        }

        if ($day->date == date('Y-m-d')) {
            $day->status = 0;
            $now = date('H:i');

            foreach ($day->times as $time) {
                if ($time->opens <= $now && $time->closes > $now) {
                    $day->status = $time->staff ? 1 : 2;
                }
            }
        }

        return $day;
    }


    /**
     * FIXME: Compatibility accessor until Twig filters are fixed.
     */
    public function __get($key)
    {
        return $this->raw->{$key};
    }

    public function info()
    {
        return $this->raw->info;
    }

    public function closed()
    {
        return $this->raw->closed;
    }

    public function opens()
    {
        if (!$this->closed()) {
            return $this->raw->opens;
        }
    }

    public function closes()
    {
        if (!$this->closed()) {
            return $this->raw->closes;
        }
    }
}
