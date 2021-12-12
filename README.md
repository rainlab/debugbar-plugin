# Debugbar Plugin

Easily see what's going on under the hood of your October CMS application.

# Installation

To install with Composer, run from your project root

```sh
composer require rainlab/debugbar-plugin
```

### Usage

Set `debug` to `true` in `config/app.php` and the debugbar should appear on your site to all authenticated backend users with the `rainlab.debugbar.access_debugbar` permission.

If you would like to make the debugbar accessible to all users regardless of authentication and permissions, then set `allow_public_access` to `true` in the configuration (see below).

See [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) for more usage instructions and documentation.

### Configuration

All configuration for the plugin is found in the **plugins/rainlab/debugbar** directory. To override any of these settings, create an override file called **config/rainlab/debugbar/config.php** in your local system.

To include exceptions in the response header of ajax calls set `debug_ajax` to `true` in **config/app.php**.

Events are not captured by default since it can slow down the front-end when many events are fired, you may enable it with the `collectors.events` setting.
