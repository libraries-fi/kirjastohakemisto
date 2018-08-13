<?php

namespace App\Controller;

use KirjastotFi\KirkantaClientBundle\DocumentTypeManager as Kirkanta;
use KirjastotFi\KirkantaClientBundle\Iterator\IterateByInitial;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ServiceController extends Controller
{
    /**
     * Alphabetical index (pro section)
     *
     * @Route("/services", name="service.index")
     * @Template("Service/index.html.twig")
     */
    public function index(Request $request, Kirkanta $kirkanta)
    {
        $result = $kirkanta->getRepository('service')->findBy([
            'lang' => $request->getLocale(),
            'refs' => [],
        ], ['type' => 'asc', 'name' => 'asc'], 3000);

        return [
            'services' => new IterateByInitial($result, 'name')
        ];
    }

    /**
     * Alphabetical index (pro section)
     *
     * @Route("/services", name="service.index")
     * @Template("Service/index-by-group.html.twig")
     */
    public function indexByType(Request $request, Kirkanta $kirkanta)
    {
        $result = $kirkanta->getRepository('service')->findBy([
            'lang' => $request->getLocale(),
            'refs' => [],
        ], ['name' => 'asc', 'type' => 'asc'], 3000);

        $groups = [];

        foreach ($result as $service) {
            $groups[$service->type()][] = $service;
        }

        return [
            'services' => $groups
        ];
    }

    /**
     * Service info page
     *
     * @Route("/services/{type}/{slug}", name="service.show")
     * @Template("Service/info.html.twig")
     */
    public function front(Request $request, Kirkanta $kirkanta, string $type, string $slug)
    {
        $result = $kirkanta->getRepository('service')->findBy([
            'lang' => $request->getLocale(),
            'refs' => [],
            'type' => $type,
            'slug' => $slug
        ]);

        foreach ($result as $service) {
            $libraries = $kirkanta->getRepository('library')->findBy([
                'lang' => $request->getLocale(),
                'refs' => ['city'],
                'with' => ['services'],
                'service' => $service->id(),
            ], ['name' => 'asc']);

            return [
                'service' => $service,
                'libraries' => $libraries
            ];
        }
        exit($slug);
    }
}
