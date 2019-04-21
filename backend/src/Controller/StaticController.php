<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\ApcuCache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class StaticController extends Controller
{
    private $httpClient;
    private $apiUrl;

    public function __construct(string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @Template("frontpage.html.twig")
     */
    public function frontpage()
    {
        return [];
    }

    /**
     * @Template("webapp.html.twig")
     */
    public function webApp()
    {
        return [];
    }

    /**
     * @Template("search.html.twig")
     */
    public function search(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('q', null, [
                'label' => 'Quick search'
            ])
            ->add('geo', CheckboxType::class, [
                'required' => false,
                'label' => 'Use geolocation'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Go'
            ])
            ->setMethod('get')
            ->getForm()
            ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->getData() + [
                'limit' => 100,
                'refs' => 'city',
                'sort' => 'city,name',
            ];

            $result = $this->httpGet('library', $query);
            $libraries = $result->items;

            $groups = [];
            foreach ($libraries as $library) {
                $groups[$library->address->city][] = $library;
            }

            ksort($groups);

            return [
                'form' => $form->createView(),
                'libraries' => $libraries,
                'libraryGroups' => $groups,
                'cities' => $result->refs->city,
            ];
        } else {
            return [
                'form' => $form->createView(),
            ];
        }
    }

    /**
     * @Template("library.html.twig")
     */
    public function library(string $city, string $library)
    {
        $result = $this->httpGet("library", [
            'city.slug' => $city,
            'slug' => $library,
            'with' => 'mailAddress,primaryContactInfo,services,schedules',
            'period.start' => '0w',
            'period.end' => '0w',
        ]);

        if ($result->total == 0) {
            // throw new NotFoundHttpException;
            return $this->forward('App\Controller\StaticController::fallback');
        }

        $library = $result->items[0];

        usort($library->services, function($a, $b) {
            return strcasecmp($a->standardName, $b->standardName);
        });

        return [
            'library' => $result->items[0]
        ];
    }

    /**
     * @Template("libraries.html.twig")
     */
    public function libraries()
    {
        $result = $this->httpGet('library', [
            'limit' => 10000,
            'sort' => 'city,name',
            'refs' => 'city'
        ]);

        $libraries = $result->items;
        $groups = [];

        foreach ($libraries as $library) {
            $groups[$library->city][] = $library;
        }

        return [
            'libraries' => $libraries,
            'libraryGroups' => $groups,
            'cities' => $result->refs->city,
        ];
    }

    /**
     * @Template("consortiums.html.twig")
     */
    public function consortiums()
    {
        $result = $this->httpGet('library', [
            'limit' => 10000,
            'sort' => 'consortium,name',
            'refs' => 'city,consortium',
        ]);

        $groups = [];

        foreach ($result->items as $library) {
            if ($cid = $library->consortium) {
                $groups[$cid][] = $library;
            }
        }

        $consortiums = get_object_vars($result->refs->consortium);

        usort($consortiums, function($a, $b) {
            return strcasecmp($a->name, $b->name);
        });

        return [
            'consortiums' => $consortiums,
            'libraryGroups' => $groups,
            'cities' => $result->refs->city,
        ];
    }

    private function httpGet(string $path, array $query) : \stdClass
    {
        // $url = "http://api.kirjastot.fi.local/v4/{$path}";
        $result = $this->http()->request('GET', $path, ['query' => $query])->getBody();
        return json_decode($result);
    }

    private function http()
    {
        if (!$this->httpClient) {
            $this->httpClient = new \GuzzleHttp\Client(['base_uri' => $this->apiUrl]);
        }
        return $this->httpClient;
    }
}
