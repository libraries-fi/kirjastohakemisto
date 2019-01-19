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

    /**
     * @Template("frontpage.html.twig")
     */
    public function frontpage()
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
            'with' => 'mailAddress,primaryContactInfo,services'
        ]);

        if ($result->total == 0) {
            throw new NotFoundHttpException;
        }

        return [
            'library' => $result->items[0]
        ];
    }

    /**
     * @Template("libraries.html.twig")
     */
    public function libraries()
    {
        return [];
    }

    /**
     * @Template("consortiums.html.twig")
     */
    public function consortiums()
    {
        return [];
    }

    /**
     * @Template("services.html.twig")
     */
    public function services()
    {
        return [];
    }

    private function httpGet(string $path, array $query) : \stdClass
    {
        $url = "http://api.kirjastot.fi.local/v4/{$path}";
        $result = $this->http()->request('GET', $url, ['query' => $query])->getBody();
        return json_decode($result);
    }

    private function http()
    {
        if (!$this->httpClient) {
            $this->httpClient = new \GuzzleHttp\Client;
        }
        return $this->httpClient;
    }
}
