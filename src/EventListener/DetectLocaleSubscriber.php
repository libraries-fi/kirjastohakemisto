<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DetectLocaleSubscriber implements EventSubscriberInterface
{
    const HEADER = 'x-hakemisto-locale';

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 17]]
        ];
    }

    public function onKernelRequest(GetResponseEvent $event) : void
    {
        $locale = $event->getRequest()->headers->get(self::HEADER);

        if ($locale) {
            $locale = substr($locale, 0, 2);
            $event->getRequest()->setLocale($locale);
        }
    }
}
