<?php

declare(strict_types=1);

namespace Plugin\ExamplePlugin\EventHandler;

use App\Event;
use NowPlaying\Result\Result;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AllTheListeners implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            Event\Radio\GenerateRawNowPlaying::class => [
                ['setListenerCount', -20]
            ],
        ];
    }

    public function setListenerCount(Event\Radio\GenerateRawNowPlaying $event)
    {
        $np_raw = $event->getResult()->toArray();

        $np_raw['listeners']['current'] = mt_rand(5, 25);
        $np_raw['listeners']['unique'] = mt_rand(0, $np_raw['listeners']['current']);
        $np_raw['listeners']['total'] = $np_raw['listeners']['current'];

        $event->setResult(Result::fromArray($np_raw));
    }
}
