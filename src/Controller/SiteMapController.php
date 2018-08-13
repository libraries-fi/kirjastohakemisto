<?php

namespace App\Controller;

use DateTime;
use KirjastotFi\KirkantaClientBundle\DocumentTypeManager as Kirkanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class SiteMapController extends Controller
{
    private $kirkanta;

    public function __construct(Kirkanta $kirkanta)
    {
        $this->kirkanta = $kirkanta;
    }

    /**
     * @Route("/robots.txt", name="robots.txt")
     */
    public function robots(Request $request)
    {
        $data = [
            sprintf('Sitemap: https://%s/sitemap.xml', $request->getHost()),
        ];

        $content = implode(PHP_EOL, $data);

        return new Response($content, 200, [
            'text/plain; charset=UTF-8'
        ]);
    }

    /**
     * @Route("/sitemap.xml", name="sitemap")
     */
    public function sitemap(Request $request)
    {
        $cache = new FilesystemCache('sitemap',  3600 * 24);
        $cache_key = $request->getLocale();

        if (!($sitemap = $cache->get($cache_key))) {
            $LANGS = [
                'fi' => 'https://hakemisto.kirjastot.fi',
                'en' => 'https://directory.libraries.fi',
                'sv' => 'https://registret.biblioteken.fi',
            ];

            uksort($LANGS, function($a, $b) use ($request) {
                $a = ($a == $request->getLocale());
                $b = ($b == $request->getLocale());
                return $b - $a;
            });

            $sitemap = ['url' => []];

            $misc_routes = [
                ['front',  []],
                ['search',  []],
                ['service.index', []],
                ['library.index', []],
                ['info', []],
#                ['contact', []],
                ['open-data', []],
            ];

            foreach ($misc_routes as $i => $config) {
                $entry = [];

                foreach ($LANGS as $lang => $domain) {
                    list($route, $params) = $config;
                    $url = $domain . $this->generateUrl($route, $params);

                    if (empty($entry['loc'])) {
                        $entry['loc'] = $url;
                    }

                    $entry['xhtml:link'][] = [
                        '@rel' => 'alternate',
                        '@hreflang' => $lang,
                        '@href' => $url,
                    ];
                }

                $sitemap['url'][] = $entry;
            }

            $libraries = $this->kirkanta->getRepository('library')->findBy([
                'with' => ['meta'],
                'refs' => ['city'],
                'type' => ['library', 'main_library'],
            ], [
                'name' => 'asc'
            ]);

            foreach ($libraries as $library) {
                $entries = [];

                foreach ($LANGS as $lang => $domain) {
                    $routes = [
                        ['library.show', [
                            'city' => $library->city()->slug()->{$lang},
                            'slug' => $library->slug()->{$lang},
                        ]],
                        ['library.services', [
                            'city' => $library->city()->slug()->{$lang},
                            'slug' => $library->slug()->{$lang},
                        ]],
                        ['library.contact', [
                            'city' => $library->city()->slug()->{$lang},
                            'slug' => $library->slug()->{$lang},
                        ]],
                        // ['library.map', [
                        //     'city' => $library->city()->slug()->{$lang},
                        //     'slug' => $library->slug()->{$lang},
                        //     // 'c' => $library->address->coordinates
                        // ]]
                    ];

                    foreach ($routes as $i => $config) {
                        list($route, $params) = $config;
                        $url = $domain . htmlspecialchars($this->generateUrl($route, $params));

                        if (empty($entries[$i]['loc'])) {
                          $entries[$i]['loc'] = $url;
                          $entries[$i]['lastmod'] = substr($library->meta->modified, 0, 10);
                          $entries[$i]['changefreq'] = 'daily';
                          $entries[$i]['priority'] = sprintf('%.1f', 1.0 - ($i / 10));
                        }

                        $entries[$i]['xhtml:link'][] = [
                            '@rel' => 'alternate',
                            '@hreflang' => $lang,
                            '@href' => $url,
                        ];
                    }
                }
                $sitemap['url'] = array_merge($sitemap['url'], $entries);
            }

            $services = $this->kirkanta->getRepository('service')->findBy([], [
                'name' => 'asc'
            ]);

            foreach ($services as $service) {
                $entry = [];

                foreach ($LANGS as $lang => $domain) {
                    $url = $domain . $this->generateUrl('service.show', [
                        'type' => $service->type,
                        'slug' => $service->slug->{$lang},
                    ]);

                    if (empty($entry['loc'])) {
                        $entry['loc'] = $url;
                        $entry['changefreq'] = 'weekly';
                    }

                    $entry['xhtml:link'][] = [
                        '@rel' => 'alternate',
                        '@hreflang' => $lang,
                        '@href' => $url,
                    ];
                }

                $sitemap['url'][] = $entry;
            }

            $sitemap['@xmlns'] = 'http://www.sitemaps.org/schemas/sitemap/0.9';
            $sitemap['@xmlns:xhtml'] = 'http://www.w3.org/1999/xhtml';

            $encoder = new XmlEncoder('urlset');
            $sitemap = $encoder->encode($sitemap, 'xml');
            $cache->set($cache_key, $sitemap);
        }

        return new Response($sitemap, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
