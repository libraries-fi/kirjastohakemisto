<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\ApcuCache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LocationController extends Controller
{
    const MUNICIPALITIES_FILE = '../data/municipality-coordinates.txt';

    private $kirkanta;
    private $cache;

    public function __construct()
    {
        $this->cache = new ApcuCache('front', 1800);
    }

    public function locate(string $city) : ?array
    {
        $fh = fopen(self::MUNICIPALITIES_FILE, 'r');
        $pos = null;

        while ($data = fgetcsv($fh, 0, "\t")) {
            if ($city == $data[0]) {
                $pos = [
                    'latitude' => $data[1],
                    'longitude' => $data[2],
                ];
                break;
            }
        }

        fclose($fh);
        return $pos;
    }

    /**
     * Tries to locate user based on IP address.
     *
     * Reasons for implementing this in the backend:
     *  1. Ability to change service providers transparently.
     *  2. Adblockers might block the actual service providers.
     *  3. Services might not allow CORS.
     */
    public function ipToLocation(Request $request)
    {
        $user_ip = $request->getClientIp();

        /**
         * Try to deduce user location from the IP address.
         */
        if (strpos($user_ip, '192.168') !== 0) {
            $cache_key = sprintf('user.location.%d', ip2long($user_ip));

            if (!($location = $this->cache->get($cache_key))) {
                try {
                    $location = file_get_contents('http://ip-api.com/json/' . $user_ip);
                    $location = json_decode($location);

                    if ($location->status == 'success' && $location->country == 'Finland') {
                        $location->pos = $this->locate($location->city);
                    }

                    $this->cache->set($cache_key, $location);
                } catch (\Exception $e) {
                    // FIXME: Logs show multiple crashes on front page so try to capture
                    // possible exception and see if it helped.
                }
            }
        }

        /**
         * Fallback to random location if IP-based positioning failed.
         */
        if (empty($location->pos)) {
            // Largest 25 cities in Finland from Wikipedia.
            $cities = [
                'Helsinki',
                'Espoo',
                'Tampere',
                'Vantaa',
                'Oulu',
                'Turku',
                'Jyväskylä',
                'Lahti',
                'Kuopio',
                'Pori',
                'Kouvola',
                'Joensuu',
                'Lappeenranta',
                'Hämeenlinna',
                'Vaasa',
                'Seinäjoki',
                'Rovaniemi',
                'Mikkeli',
                'Kotka',
                'Salo',
                'Porvoo',
                'Kokkola',
                'Lohja',
                'Hyvinkää',
                'Järvenpää',
            ];

            $city = $cities[rand(0, count($cities) - 1)];
            $pos = $this->locate($city);

            // Mimic the structure of Position object received from navigator.geolocation
            $location = (object)[
                'coords' => $pos,
                'timestamp' => (int)(microtime(true) * 1000),
                'city' => $city,
                'random' => true,
            ];
        }

        return new JsonResponse($location);
    }
}
