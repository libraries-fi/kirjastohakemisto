<?php

namespace App\Controller;

use DateTime;
use KirjastotFi\KirkantaClientBundle\DocumentTypeManager as Kirkanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonLdController extends Controller
{
    /**
     * Export the library as JSON-LD.
     *
     * @Route("/export/organisation/{id}", name="library.export")
     */
    public function library(Request $request, Kirkanta $kirkanta, int $id) : JsonResponse
    {
        /*
         * FIXME: If we decide to display the whole organisation hierarchy on the website, this
         * in field 'parentOrganization'.
         * document could also contain information about the parent organisation
         */

        $result = $kirkanta->getRepository('library')->findBy([
            'id' => $id,
            'lang' => $request->getLocale(),
            'with' => ['extra', 'links', 'pictures', 'schedules', 'phone_numbers'],
            'refs' => ['city', 'consortium', 'period'],

            'period.start' => '0w',
            'period.end' => '7w',
        ]);

        $schedules = [];

        foreach ($result as $library) {
            foreach (array_chunk($library->schedules, 7) as $week) {
                foreach ($week as $i => $day) {
                    if (!isset($result->refs->period->{$day->period})) {
                        /*
                         * FIXME: API returns days that should've been removed,
                         * if the period has been removed but no other period covers
                         * the same range.
                         */
                        continue;
                    }
                    if (!isset($schedules[$day->period])) {
                        $schedules[$day->period] = (object)[];
                    }
                    $period = $result->refs->period->{$day->period};
                    $schedules[$period->id]->rules[$i] = $day;
                    $schedules[$period->id]->period = $period;

                    if ($period->valid_until) {
                        // Simple heuristic to decide whether a range period is 'special' or not.
                        $diff = (new DateTime($period->valid_from))->diff(new DateTime($period->valid_until));
                        $schedules[$day->period]->special = $diff->format('a') <= 7;
                    } else {
                        $schedules[$day->period]->special = false;
                    }
                }
            }

            $export = [
                '@context' => [
                    'http://schema.org',
                    ['@language' => $request->getLocale()]
                ],
                '@type' => 'Library',
                'name' => $library->name,
                'email' => $library->email,
                'publicAccess' => true,

                /*
                 * Google displays a warning about not having this.
                 * Not sure if should still drop this field.
                 */
                'priceRange' => 'Services are free',

                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'Finland',
                    'addressLocality' => $library->address->city,
                    'postalCode' => $library->address->zipcode,
                    'streetAddress' => $library->address->street,
                ]
            ];

            if ($library->extra->founded) {
                $export['foundingDate'] = (string)$library->extra->founded;
            }

            if ($library->address->coordinates) {
                $export['geo'] = [
                    '@type' => 'GeoCoordinates',
                    'latitude' => $library->address->coordinates->lat,
                    'longitude' => $library->address->coordinates->lon,
                ];
            }

            if (count($library->phone_numbers)) {
                $export['telephone'] = $library->phone_numbers[0]->number;
            }

            foreach ($library->pictures as $picture) {
                $export['image'] = [
                    '@type' => 'ImageObject',
                    'caption' => $picture->name,
                    'thumbnail' => $picture->files->small,
                    'url' => $picture->files->large,
                ];
            }

            foreach ($library->pictures as $picture) {
                $export['photos'][] = [
                    '@type' => 'Photograph',
                    'description' => $picture->name,
                    'url' => $picture->files->large,
                ];
            }

            foreach ($schedules as $period) {
                foreach ($period->rules as $day) {
                    if ($period->special) {
                        $row = [
                            '@type' => 'OpeningHoursSpecification',
                            'dayOfWeek' => date('l', strtotime($day->date)),
                            'validFrom' => $period->period->valid_from,
                            'validThrough' => $period->period->valid_until
                        ];

                        if (!$day->closed) {
                            $row['opens'] = $day->opens;
                            $row['closes'] = $day->closes;
                        }

                        $export['specialOpeningHoursSpecification'][] = $row;
                    } elseif (!$day->closed) {
                        $name = substr(date('D', strtotime($day->date)), 0, 2);
                        $export['openingHours'][] = sprintf('%s %s-%s', $name, $day->opens, $day->closes);
                    }
                }
            }

            // header('Content-Type: application/json');
            // print json_encode($export, JSON_PRETTY_PRINT);
            // exit;
            return new JsonResponse($export);
        }

        exit('here');
    }
}
