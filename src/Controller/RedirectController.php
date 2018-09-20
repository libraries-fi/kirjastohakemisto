<?php

namespace App\Controller;

use KirjastotFi\KirkantaClientBundle\DocumentTypeManager as Kirkanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\ApcuCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectController extends Controller
{
    use Helper\PageSizeTrait;
    use Helper\TranslatorAccessTrait;

    const MUNICIPALITIES_FILE = '../data/municipalities_coordinates.txt';

    private $kirkanta;
    private $cache;

    public function __construct(Kirkanta $kirkanta)
    {
        $this->kirkanta = $kirkanta;
    }

    /**
     * Handle cases where the URL contains only a slug of 'something'.
     *
     * @Route("/{slug}", name="slug.redirect", requirements={"slug": "[\w\-]+"})
     */
    public function slugRedirect(Request $request, string $slug)
    {
        $result = $this->kirkanta->getRepository('library')->findBy([
            'lang' => $request->getLocale(),
            'slug' => $slug,
            'refs' => 'city',
        ]);

        foreach ($result as $library) {
            if ($library->city()) {
                return $this->redirectToRoute('library.show', [
                    'city' => $library->city()->slug(),
                    'slug' => $library->slug(),
                ], 301);
            }
        }

        /*
         * FIXME: There is a bug in API v3 that breaks the search with langcode sometimes.
         *
         * Workaround is not to pass langcode. Slug has to be unique regardless of language, so it
         * isn't an issue.
         */
        $result = $this->kirkanta->getRepository('city')->findBy([
            'slug' => $slug,
            'lang' => $request->getLocale(),
        ]);

        foreach ($result as $city) {
            return $this->redirectToRoute('search', [
                'm' => $city->slug
            ], 301);
        }

        throw new NotFoundHttpException;
    }
}
