<?php

namespace App\Controller;

use KirjastotFi\KirkantaClientBundle\DocumentTypeManager as Kirkanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\ApcuCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FrontController extends Controller
{
    use Helper\PageSizeTrait;
    use Helper\TranslatorAccessTrait;

    const MUNICIPALITIES_FILE = '../data/municipalities_coordinates.txt';

    private $kirkanta;
    private $cache;

    public function __construct(Kirkanta $kirkanta)
    {
        $this->kirkanta = $kirkanta;
        $this->cache = new ApcuCache('front', 1800);
    }

    public function locate(string $city) : array
    {
        $fh = fopen(self::MUNICIPALITIES_FILE, 'r');
        $pos = [];

        while ($data = fgetcsv($fh, 0, "\t")) {
            if ($city == $data[0]) {
                $pos = [$data[1], $data[2]];
                break;
            }
        }

        fclose($fh);
        return $pos;
    }

    /**
     * @Route("/", name="front", requirements={"page": "\d+"})
     * @Template("frontpage.html.twig")
     */
    public function frontpage(Request $request)
    {
        $sort = ['distance' => 'asc', 'name' => 'asc'];
        $city_name = null;

        $DISTANCE = 35;
        $LIMIT = 40;

        $query = [
            'distance' => $DISTANCE,
            'period.start' => '0d',
            'period.end' => '1d',
            'with' => ['pictures', 'schedules', 'extra'],
            'refs' => ['city'],
            'lang' => $request->getLocale(),
        ];

        $user_ip = $request->getClientIp();

        /*
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

            $location = (object)[
                'city' => $city,
                'pos' => $pos,
            ];
        }

        $query['geo'] = sprintf('%2.6f,%2.6f', ...$location->pos);

        $result = $this->kirkanta->getRepository('library')->findBy($query, $sort, $LIMIT);
        $libraries = iterator_to_array($result);

        if ($libraries && !empty($libraries[0]->address->city)) {
            // This way we can utilize translated city names from Kirkanta!
            $location->city = $libraries[0]->address->city;
        }

        usort($libraries, function($a, $b) {
            if ($a->pictures() ^ $b->pictures()) {
                return $a->pictures() ? -1 : 1;
            } else {
                return rand(-1, 1);
            }
        });

        $libraries = array_slice($libraries, 0, 10);

        return [
            'libraries' => $libraries,
            'location' => $location,
            'page' => 1,
        ];
    }

    /**
     * @Route("/info", name="info")
     * @Template("info.html.twig")
     */
    public function info()
    {

    }

    /**
     * @Route("/contact", name="contact")
     * @Template("contact.html.twig")
     */
    public function contact()
    {

    }

    /**
     * @Route("/open-data", name="open-data")
     * @Template("open-data.html.twig")
     */
    public function openData()
    {

    }

    /**
     * @Route("/info/widgets")
     * @Template("widget-builder.html.twig")
     */
    public function widgetBuilder()
    {

    }
}
