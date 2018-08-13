<?php

namespace App\Controller;

use App\Form\LibrarySearchForm;
use KirjastotFi\KirkantaClientBundle\DocumentTypeManager as Kirkanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\ApcuCache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    use Helper\PageSizeTrait;
    use Helper\TranslatorAccessTrait;

    private $kirkanta;
    private $cache;

    public function __construct(Kirkanta $kirkanta)
    {
        $this->kirkanta = $kirkanta;
        $this->cache = new ApcuCache('search', 1800);
    }

    /**
     * Simple library search.
     *
     * @Route("/search/{page}", name="search", requirements={"page": "\d+"})
     * @Route("/search/{page}", name="library.search", requirements={"page": "\d+"})
     * @Template("search.html.twig")
     */
    public function search(Request $request, int $page = 1)
    {
        $form = $this->createForm(LibrarySearchForm::class, [
            'cities' => $this->loadCities($request->getLocale()),
        ]);

        $skip = max($this->pageSize() * ($page - 1), 0);
        $sort = ['name' => 'asc'];

        $query = [
            // 'city.name' => 'helsinki,espoo,vantaa',
            'period.start' => '0d',
            'period.end' => '1d',
            'with' => ['pictures', 'schedules'],
            'refs' => ['city'],
            'lang' => $request->getLocale(),
        ];

        $user_input = array_replace($request->query->all(), $request->request->all());

        $form->submit($user_input);

        if ($form->isSubmitted() && $form->isValid()) {
            $values = $form->getData();

            if (isset($user_pos)) {
                $query['geo'] = $user_pos;
                $query['distance'] = '3';
            }

            if (!empty($values['m'])) {
                $query['city.slug'] = $values['m'];
            }

            if (!empty($values['s'])) {
                $query['service'] = $values['s'];
            }

            if (!empty($values['q'])) {
                $query['q'] = $values['q'];
                $sort = [];
            }

            if (!empty($values['o'])) {
              $query['open'] = 1;
            }

            if (!empty($values['t'])) {
                $query['branch_type'] = [];

                foreach ($values['t'] as $type) {
                    switch ($type) {
                        // case 'c':
                        //     $query['branch_type'] = array_merge($query['branch_type'], [
                        //         'mobile',
                        //     ]);
                        //     break;
                        // case 'e':
                        //     $query['branch_type'] = array_merge($query['branch_type'], [
                        //         'polytechnic',
                        //         'university',
                        //         'vocational_college',
                        //         'school',
                        //     ]);
                        //     break;
                        case 'o':
                            $query['branch_type'] = array_merge($query['branch_type'], [
                                'home_service',
                                'institutional',
                                'children',
                                'other',
                            ]);
                            break;
                        case 'p':
                            $query['branch_type'][] = 'polytechnic';
                            break;
                        case 'r':
                            $query['branch_type'] = array_merge($query['branch_type'], [
                                'library',
                                'main_library',
                                'mobile',
                                'regional',
                            ]);

                            break;
                        case 's':
                            $query['branch_type'] = array_merge($query['branch_type'], [
                                'special',
                            ]);
                            break;
                        case 'u':
                            $query['branch_type'] = array_merge($query['branch_type'], [
                                'university',
                            ]);
                            break;
                        // case 'v':
                        //     $query['branch_type'] = array_merge($query['branch_type'], [
                        //         'vocational_college',
                        //     ]);
                        //     break;
                    }
                }
            }

            /*
             * NOTE: For privacy reasons we don't embed the coordinates on the Form object
             * because the fields will also be rendered to the HTML page and possibly even
             * passed as query variables (browser history etc.)
             *
             * Hence read the coordinates from raw data $user_input.
             */
            if (!empty($user_input['pos'])) {
              $query['geo'] = $user_input['pos'];
              $query['distance'] = '1000';

              $sort = ['distance' => 'asc'];
            }
        }

        if ($request->isXmlHttpRequest()) {
            $result = $this->kirkanta->getRepository('library')->findBy($query, $sort, $this->pageSize(), $skip);
            return new JsonResponse($result);
        }

        return [
            'search_form' => $form->createView(),
            'page_title' => $this->tr('Find your library'),
            // 'items' => $result,
            'items' => [],
            'page' => $page,
        ];
    }

    private function loadCities(string $locale) : array
    {
        $cache_key = 'cities.' . $locale;

        if (!($cities = $this->cache->get($cache_key))) {
            $result = $this->kirkanta->getRepository('city')->findBy(['lang' => $locale], ['name' => 'asc']);
            $cities = [];

            foreach ($result as $city) {
                $cities[$city->slug] = $city->name;
            }

            $this->cache->set($cache_key, $cities);
        }

        return $cities;
    }
}
