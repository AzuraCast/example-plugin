<?php

declare(strict_types=1);

use App\Event;
use Azura\SlimCallableEventDispatcher\CallableEventDispatcherInterface;

return function (CallableEventDispatcherInterface $dispatcher)
{
    // Add the "example:list-stations" command to the CLI prompt.
    $dispatcher->addListener(Event\BuildConsoleCommands::class, function (Event\BuildConsoleCommands $event) {
        $event->addAliases([
            'example:list-stations' => \Plugin\ExamplePlugin\Command\ListStations::class,
        ]);
    }, -1);

    // Tell the view handler to look for templates in this directory too
    $dispatcher->addListener(Event\BuildView::class, function(Event\BuildView $event) {
        $event->getView()->addFolder('example', __DIR__.'/templates');
    });

    // Add a new route handled exclusively by the plugin.
    $dispatcher->addListener(Event\BuildRoutes::class, function(Event\BuildRoutes $event) {
        $app = $event->getApp();

        $app->get('/example', \Plugin\ExamplePlugin\Controller\HelloWorld::class)
            ->setName('example-plugin:index:index')
            ->add(\App\Middleware\EnableView::class);
    });

    // You can also add classes that implement the EventSubscriberInterface
    $dispatcher->addSubscriber(new \Plugin\ExamplePlugin\EventHandler\AllTheListeners);
};
