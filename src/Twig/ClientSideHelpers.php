<?php

namespace App\Twig;

use DateTime;
use stdClass;
use Traversable;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\LoaderInterface;
use Twig\TwigFunction;
use KirjastotFi\KirkantaClientBundle\Entity\ScheduleDay;
use App\Util\TranslatorTrait;
use Symfony\Component\Translation\TranslatorInterface;

class ClientSideHelpers extends AbstractExtension
{
    private $loader;
    private $twig;

    public function __construct(LoaderInterface $loader, Environment $twig)
    {
        $this->loader = $loader;
        $this->twig = $twig;
    }

    public function getName() : string
    {
        return 'kirjastot_fi_client_side_helpers';
    }

    public function getFunctions() : array
    {
        return [
            new TwigFunction('template', [$this, 'readTemplate'])
        ];
    }

    public function readTemplate($name) : string
    {
        $source = $this->loader->getSourceContext($name)->getCode();
        $encoded = base64_encode($source);
        return $encoded;
    }
}
