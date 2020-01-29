# Debugbar Plugin

Easily see what's going on under the hood of your October application.

# Installation

To install from the [Marketplace](https://octobercms.com/plugin/rainlab-debugbar), click on the "Add to Project" button and then select the project you wish to add it to and pay for the plugin. Once the plugin has been added to the project, go to the backend and check for updates to pull in the plugin.

To install from the backend, go to **Settings -> Updates & Plugins -> Install Plugins** and then search for `RainLab.DebugBar`.

To install from [the repository](https://github.com/rainlab/debugbar-plugin), clone it into **plugins/rainlab/debugbar** and then run `composer update` from your project root in order to pull in the dependencies.

To install it with Composer, run `composer require rainlab/debugbar-plugin` from your project root.

### Usage

Set `debug` to `true` in `config/app.php`, and the debugbar should appear in your backend. See [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) for more usage instructions and documentation.

To include exceptions in the response header of ajax calls set `debugAjax` to `true` in `config/app.php`.