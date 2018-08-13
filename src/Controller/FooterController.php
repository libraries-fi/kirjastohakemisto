<?php

namespace App\Controller;

use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\ApcuCache;
use Symfony\Component\HttpFoundation\Request;

class FooterController extends Controller
{
    const BASE_URL = 'https://gfx.kirjastot.fi/shared-footer/';
    /**
     * Export the library as JSON-LD.
     *
     * @Route("/export/footer", name="export.footer")
     * @Template("component/footer.html.twig")
     */
    public function footer(Request $request)
    {
        $cache = new ApcuCache("shared-footer", 3600);
        $cache_key = $request->getLocale();

        if (!($result = $cache->get($cache_key))) {
            $url = self::BASE_URL . '?lang=' . $request->getLocale();

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'X-Requested-With: XMLHttpRequest'
            ]);

            $data = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($data, true);

            $cache->set($cache_key, $result);
        }

        $result['base_url'] = self::BASE_URL;

        return $result;
    }
}
