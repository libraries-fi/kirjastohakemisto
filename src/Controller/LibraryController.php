<?php

namespace App\Controller;

use KirjastotFi\KirkantaClientBundle\DocumentTypeManager as Kirkanta;
use KirjastotFi\KirkantaClientBundle\Iterator\IterateByInitial;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LibraryController extends Controller
{
    use Helper\PageSizeTrait;
    use Helper\TranslatorAccessTrait;

    /**
     * Alphabetical index (pro section)
     *
     * @Route("/libraries", name="library.index")
     * @Template("Library/index.html.twig")
     */
    public function index(Request $request, Kirkanta $kirkanta)
    {
        $result = $kirkanta->getRepository('library')->findBy([
            'lang' => $request->getLocale(),
            'refs' => ['city'],
            'type' => ['library', 'main_library'],
        ], ['name' => 'asc'], 3000);

        return [
            'libraries' => new IterateByInitial($result, 'name'),
        ];
    }

    /**
     * Index by consortium (pro section)
     *
     * @Route("/libraries/by-consortium", name="library.index_by_consortium")
     * @Template("Library/index_by_consortium.html.twig")
     */
    public function indexByConsortiumAction(Request $request, Kirkanta $kirkanta)
    {
        $result = $kirkanta->getRepository('library')->findBy([
            'lang' => $request->getLocale(),
            'special' => false,
            'refs' => ['city', 'consortium'],
        ], ['consortium.name' => 'asc', 'name' => 'asc'], 3000);

        $groups = [];

        foreach ($result as $library) {
            if (!$library->consortium() || $library->consortium()->isSpecial()) {
                continue;
            }
            $groups[$library->consortium()->id()][] = $library;
        }

        return [
            'result' => $groups,
        ];
    }

    /**
     * Index by city (pro section)
     *
     * @Route("/libraries/by-city", name="library.index_by_city")
     * @Template("Library/index_by_city.html.twig")
     */
    public function indexByCityAction(Request $request, Kirkanta $kirkanta)
    {
        $result = $kirkanta->getRepository('library')->findBy([
            'lang' => $request->getLocale(),
            'refs' => ['city'],
        ], ['city.name' => 'asc', 'name' => 'asc'], 3000);

        $groups = [];

        foreach ($result as $library) {
            if (!$library->city()) {
                continue;
            }
            $groups[$library->city()->id()][] = $library;
        }

        return [
            'result' => $groups
        ];
    }

    /**
     * Map page for a library.
     *
     * @Route("/{city}/{slug}/map", name="library.map")
     * @Template("Library/map.html.twig")
     */
    // public function mapPageAction(Request $request, $city, $slug, Kirkanta $kirkanta)
    // {
    //     $sections = ['mail_address', 'services'];
    //     $refs = ['city', 'consortium'];
    //     list($library, $_) = $this->load($request, $kirkanta, $city, $slug, $sections, $refs);
    //
    //     $coordinates = $request->query->get('c');
    //     $limit = 50;
    //     $result = $kirkanta->getRepository('library')->findBy([
    //         'with' => ['pictures', 'schedules'],
    //         'refs' => ['city'],
    //         'lang' => $request->getLocale(),
    //         'geo' => $coordinates,
    //         'distance' => 12,
    //     ], ['name' => 'asc'], $limit);
    //
    //     return [
    //         'library' => $library,
    //         'libraries' => $result,
    //         'coordinates' => $coordinates,
    //     ];
    // }

    /**
     * The main page of a library.
     *
     * @Route("/{city}/{slug}", name="library.show")
     * @Template("Library/library.html.twig")
     */
    public function front(Request $request, $city, $slug, Kirkanta $kirkanta)
    {
        $sections = ['extra', 'links', 'pictures', 'schedules', 'phone_numbers', 'services'];
        $refs = ['city', 'consortium', 'period'];
        $params = [
            'period.start' => '0w',
            'period.end' => '11w',
        ];

        list($library, $refs_array) = $this->load($request, $kirkanta, $city, $slug, $sections, $refs, $params);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($library);
        }

        return [
            'library' => $library,
            'refs' => $refs_array,
        ];
    }

    /**
     * The main page of a library.
     *
     * @Route("/{city}/{slug}/services", name="library.services")
     * @Template("Library/services.html.twig")
     */
    public function servicesPageAction(Request $request, $city, $slug, Kirkanta $kirkanta)
    {
        $sections = ['extra', 'links', 'pictures', 'services'];
        $refs = ['city', 'consortium'];

        list($library, $refs_array) = $this->load($request, $kirkanta, $city, $slug, $sections, $refs);

        return [
            'library' => $library,
            'refs' => $refs_array,
        ];
    }

    /**
     * Contact details page for a library.
     *
     * @Route("/{city}/{slug}/contact", name="library.contact")
     * @Template("Library/contact.html.twig")
     */
    public function contactPageAction(Request $request, $city, $slug, Kirkanta $kirkanta)
    {
        $sections = ['extra', 'links', 'mail_address', 'phone_numbers', 'pictures', 'persons', 'services'];
        $refs = ['city', 'consortium'];

        list($library, $refs_array) = $this->load($request, $kirkanta, $city, $slug, $sections, $refs);

        return [
            'library' => $library,
            'refs' => $refs_array,
        ];
    }

    private function load(Request $request, Kirkanta $kirkanta, $city, $slug, array $with = null, array $refs = null, array $extra = [])
    {
        $result = $kirkanta->getRepository('library')->findBy([
            'slug' => $slug,
            'city.slug' => $city,
            'lang' => $request->getLocale(),
            'with' => $with,
            'refs' => $refs,
        ] + $extra);

        $refs_array = [];

        foreach ($result->refs as $type => $items_map) {
            $refs_array[$type] = [];
            foreach ($items_map as $id => $data) {
                $refs_array[$type][$id] = $data;
            }
        }

        foreach ($result as $library) {
            return [$library, $refs_array];
        }

        throw $this->createNotFoundException(sprintf('Nothing found for slug \'%s\'', $slug));
    }
}
