# Extending AzuraCast with Plugins

This repository contains documentation for the AzuraCast plugin system, as well as a working example of some of the more common modifications that might be made to the AzuraCast system via plugins.

## Including Plugins

Plugins are automatically discovered if they're located in the `/plugins` directory relative to the main AzuraCast installation. Plugins are ignored by the parent AzuraCast instance, so you can update your instance any time you like without worrying about your plugins being removed.

### Traditional Installations

You should clone the repository for your plugin directly into the `plugins` directory, like so:

```bash
mkdir -p /var/azuracast/www/plugins/example-plugin
cd /var/azuracast/www/plugins/example-plugin
git clone https://github.com/AzuraCast/example-plugin.git .
```

### Docker Installations

You can clone the plugin directory anywhere you want on the host machine, then update your `docker-compose.yml` to mount the plugin as a volume in the correct location, like so:

```yaml
version: '2.2'

services:
  web:
    # image, depends_on, environment, etc...
    volumes:
      - ./path_to_plugin:/var/azuracast/www/plugins/example-plugin
      - www_data:/var/azuracast/www
      - tmp_data:/var/azuracast/www_tmp
```

Make sure to restart the Docker containers afterward (using `docker-compose down` and `docker-compose up -d`).

## Naming Convention

### Autoloaded Files

The following files are automatically loaded along with the relevant section of code:

 - `/services.php`: Register or extend services with the application's Dependency Injection (DI) container.
 - `/events.php`: Register listeners for the Event Dispatcher.

### Classes

AzuraCast autoloads PHP files inside `/plugins/(plugin_name)/src` as long as they are in the appropriate namespace.

The function used to convert from the plugin folder name into the PHP class name is:

```php
<?php
return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
```

For example, `/plugins/example-plugin/src` will autoload classes in the `\Plugin\ExamplePlugin` namespace.

## The Event Dispatcher

Most of the extensibility of plugins comes from events that use the EventDispatcher in AzuraCast. Both classes from inside AzuraCast and plugins are registered as "listeners" to common events that are dispatched by the system, so you can override or modify the core application's responses simply by adding your own listeners in the right order.

Here is an example of a basic `events.php` file for handling an event:

```php
<?php
return function (\App\EventDispatcher $dispatcher)
{
    $dispatcher->addListener(\App\Event\BuildRoutes::class, function(\App\Event\BuildRoutes $event) {
        $app = $event->getApp();

        // Modify the app's routes here
    }, -5);
};
```

As you can see, each event listener that you register has to provide the event that it listens to as a callable to the `addListener` method's first parameter, and each event listener receives an instance of that event class complete with relevant metadata already attached. Listeners also have a priority (the last argument in the function call); this number can be positive or negative, with the default handler tending to be around zero. Higher numbers are dispatched before lower numbers.

Below is a listing of the events that can be overridden by plugins:

### `\App\Event\BuildConsoleCommands`

- [Class reference](https://github.com/AzuraCast/AzuraCast/blob/master/src/Event/BuildConsoleCommands.php)

This event allows you to register your own CLI console commands, which appear when running the [AzuraCast CLI](http://www.azuracast.com/cli.html).

### `\App\Event\BuildRoutes`

- [Class reference](https://github.com/AzuraCast/AzuraCast/blob/master/src/Event/BuildRoutes.php)

This event allows you to register custom routes to the HTTP Router. This allows you to create entirely new routes handled exclusively by your plugins.

### `\App\Event\BuildView`

- [Class reference](https://github.com/AzuraCast/AzuraCast/blob/master/src/Event/BuildView.php)

This event lets you inject custom data into the template renderer, or modify the existing data that's already injected. This includes the current user, current station, page title, etc.

### `\App\Event\SendWebhooks`

- [Class reference](https://github.com/AzuraCast/AzuraCast/blob/master/src/Event/SendWebhooks.php)

This event is triggered any time web hooks are triggered for a station. It includes the current "now playing" data along with a list of the triggers that are associated with the webhook (i.e. if the song changed, DJ connected/disconnected, etc).

### `\App\Event\Radio\AnnotateNextSong`

- [Class reference](https://github.com/AzuraCast/AzuraCast/blob/master/src/Event/Radio/AnnotateNextSong.php)

This event is triggered once the next song has been determined by the AzuraCast AutoDJ software and is being sent to Liquidsoap. Annotations allow you to customize fade-in, fade-out, cue-in and cue-out data, and the artist/title displayed for a song.

### `\App\Event\Radio\GenerateRawNowPlaying`

- [Class reference](https://github.com/AzuraCast/AzuraCast/blob/master/src/Event/Radio/GenerateRawNowPlaying.php)

This event is triggered when building the "Now Playing" data for a given station. This data is called "raw" because it has not been converted yet into the standardized API response format served by AzuraCast's API. By modifying the "raw" nowplaying response, you can change the currently playing song or modify the listener count.

### `\App\Event\Radio\GetNextSong`

- [Class reference](https://github.com/AzuraCast/AzuraCast/blob/master/src/Event/Radio/GetNextSong.php)

This event is triggered as the AzuraCast AutoDJ is determining the next song to play for a given station. By default, ths checks for an existing "next song" record in the database, and if one isn't present, determines what should play next based on all available playlists and their scheduling status.

### `\App\Event\Radio\WriteLiquidsoapConfiguration`

- [Class reference](https://github.com/AzuraCast/AzuraCast/blob/master/src/Event/Radio/WriteLiquidsoapConfiguration.php)

This event is triggered when changes to the Liquidsoap configuration are being written to disk. This process uses the Event Dispatcher because it is by far the most complex configuration file written by the system, and there are multiple points at which one may want to override the configuration written by AzuraCast itself. Users are already able to write custom configuration to one specific location (between the playlists being built and mixed with the live signal, and the signal being broadcast to local and remote sources), but overriding this event allows you to modify the configuration in any other location.
