<?php namespace Bedard\Debugbar;

use App;
use Event;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;

/**
 * Debugbar Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Debugbar',
            'description' => 'Debugbar integration for OctoberCMS.',
            'author'      => 'Bedard',
            'icon'        => 'icon-cog'
        ];
    }

    /**
     * Register service provider, Twig extensions, and alias facade.
     */
    public function boot()
    {
        // Service provider
        App::register('\Barryvdh\Debugbar\ServiceProvider');

        // Register alias
        $alias = AliasLoader::getInstance();
        $alias->alias('Debugbar', '\Barryvdh\Debugbar\Facade');

        // Register middleware
        if (\Config::get('app.debugAjax', false)) {
            $this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware('\Bedard\Debugbar\Middleware\Debugbar');
        }

        // Twig extensions
        Event::listen('cms.page.beforeDisplay', function($controller, $url, $page) {
            $twig = $controller->getTwig();
            $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Debug($this->app));
            $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Stopwatch($this->app));
        });
    }

}
