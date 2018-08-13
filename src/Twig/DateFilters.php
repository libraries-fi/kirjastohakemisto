<?php

namespace App\Twig;

use DateTime;
use IntlDateFormatter;
use stdClass;
use Traversable;
use Twig\Extension\AbstractExtension;
use Twig\Environment;
use Twig\TwigFilter;
use KirjastotFi\KirkantaClientBundle\Entity\ScheduleDay;
use App\Util\TranslatorTrait;
use Symfony\Component\Translation\TranslatorInterface;

class DateFilters extends AbstractExtension
{
    use TranslatorTrait;

    const CLOSED = 0;
    const OPEN = 1;
    const SELF_SERVICE = 2;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getName()
    {
        return 'kirjastot_fi_date_filters';
    }

    public function getFilters()
    {
        return [
            new TwigFilter('openTwoDays', [$this, 'openTwoDays'], ['is_safe' => ['html']]),
            new TwigFilter('smartStatus', [$this, 'smartStatus'], ['is_safe' => ['html']]),
            new TwigFilter('statusDescription', [$this, 'statusDescription'], ['is_safe' => ['html']]),
            new TwigFilter('dateloc', [$this, 'localizedDate'], ['needs_environment' => true]),
        ];
    }

    public function statusDescription(iterable $times)
    {
        if ($times instanceof Traversable) {
            $times = iterator_to_array($times);
        }

        if (empty($times)) {
            return;
        }

        $today = array_shift($times);
        $tomorrow = array_shift($times);

        if ($time = $this->openTime($today)) {
            $status = $this->isOpen($today);

            switch ($status) {
                case self::OPEN:
                case self::SELF_SERVICE:
                    // return sprintf('Until %s', $time[1]);
                    return sprintf('%s – %s', ...$time);

                case self::SELF_SERVICE:
                    return sprintf('Self-service until %s', $time[1]);

                case self::CLOSED:
                    $now = date('H:i');
                    if ($now < $time[1]) {
                        return sprintf('Opens at %s', $time[0]);
                    }
            }
        }

        if ($time = $this->openTime($tomorrow)) {
            $html = sprintf('<span style="white-space: nowrap">%s – %s</span>', ...$time);
            return sprintf($this->tr('Tomorrow %s'), $html);
        }

        // Opening time cannot be deduced right now.
        return sprintf('<em>%s</em>', $this->tr('Check schedules'));
    }

    public function smartStatus(iterable $times)
    {
        if ($times instanceof Traversable) {
            $times = iterator_to_array($times);
        }

        if (empty($times)) {
            return;
        }

        $today = array_shift($times);
        $tomorrow = array_shift($times);
        $message = [];

        if ($time = $this->openTime($today)) {
            $status = $this->isOpen($today);
            $class = ['time-closed', 'time-open', 'time-self-service'][$status];

            switch ($status) {
                case self::OPEN:
                    $label = sprintf($this->tr('Open until %s'), $time[1]);
                    $message[] = sprintf('<span class="%s">%s</span>', $class, $label);
                    break;

                case self::SELF_SERVICE:
                    $label = sprintf($this->tr('Self-service until %s'), $time[1]);
                    $message[] = sprintf('<span class="%s">%s</span>', $class, $label);
                    break;

                case self::CLOSED:
                    if ($time[0] > date('H:i')) {
                        $label = sprintf('Opens at %s', $time[0]);
                        $message[] = sprintf('<span class="%s">%s</span>', $class, $label);
                    }
                    break;
            }
        } else if ($time = $this->openTime($tomorrow)) {
            $status = $this->isOpen($tomorrow);
            $class = ['time-closed', 'time-open', 'time-self-service'][$status];

            if ($status) {
                $label = sprintf($this->tr('Opens tomorrow at %s'), $time[1]);
                $message[] = sprintf('<span class="time-closed">%s</span>', $label);
            }
        } else {
            $label = $this->tr('Might open on Jan 1 2020 at 08:00');
            $message[] = sprintf('<span class="time-closed">%s</span>', $label);
        }

        return implode(' ', $message);
    }

    public function openTwoDays($times)
    {
        if ($times instanceof Traversable) {
            $times = iterator_to_array($times);
        }

        if (empty($times)) {
            return;
        }

        $today = array_shift($times);
        $tomorrow = array_shift($times);
        $message = [];

        if ($time = $this->openTime($today)) {
            $status = $this->isOpen($today);
            $class = ['time-closed', 'time-open', 'time-self-service'][$status];
            $label = sprintf('%s %s – %s', $this->tr('Open'), ...$time);
            $message[] = sprintf('<span class="%s">%s</span>', $class, $label);

            if ($status == self::SELF_SERVICE) {
                $message[] = sprintf('<span class="sr-only">%s</span>', $this->tr('Currently self service'));
            }
        } else {
            $message[] = sprintf('<span class="time-closed">%s</span>', $this->tr('Closed today'));
        }

        if ($tomorrow) {
            if ($tomorrow->closed) {
                $message[] = sprintf('<span class="tag">(%s)</span>', $this->tr('Closed tomorrow'));
            } else {
                $open = $this->openTime($tomorrow);
                $message[] = sprintf('<span class="tag">%s %s – %s</span>', $this->tr('Tomorrow'), ...$open);
            }
        }

        return implode(' ', $message);
    }

    public function openTime(ScheduleDay $day)
    {
        if ($day->closed) {
            return null;
        }

        return [$day->opens, $day->closes];
    }

    /**
     * Return a status code based on opening times.
     *
     * @return int
     */
    public function isOpen(ScheduleDay $day)
    {
        if (is_null($day->status)) {
            return null;
        }
        return $day->status;
    }

    public function localizedDate(Environment $env, string $date, string $format = 'long-ish') : string
    {
        $locale = $env->getGlobals()['app']->getRequest()->getLocale();

        if ($format == 'long-ish') {
            $format = 'long';

            // Function defined in Twig\Extensions\Extension\Intl from twig/extensions.
            $value = twig_localized_date_filter($env, $date, $format, 'none', null);

            if (date('Y') == substr($value, -4)) {
                $value = substr($value, 0, -5);
            }
        }

        return $value;
    }

    private function isClosed(ScheduleDay $day)
    {
        return $day->closed || !($this->isOpenNormal() || $this->isOpenSelfService());
    }

    private function isOpenNormal(ScheduleDay $day)
    {
        $now = date('H:i');

        foreach ($day->times as $time) {
            if ($time->opens <= $now && $now <= $time->closes) {
                return true;
            }
        }

        return false;
    }

    private function isOpenSelfService(ScheduleDay $day)
    {
        if (isset($day->sections->selfservice)) {
            $now = date('H:i');
            foreach ($day->sections->selfservice->times as $time) {
                if ($time->opens <= $now && $now <= $time->closes) {
                    return true;
                }
            }
        }
        return false;
    }
}
