<?php namespace Bedard\Debugbar;

use App;
use Event;
use System\Classes\PluginBase;

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
     * Register service provider and Twig extensions.
     */
    public function boot()
    {
        // Service provider
        App::register('\Barryvdh\Debugbar\ServiceProvider');

        // Twig extensions
        Event::listen('cms.page.beforeDisplay', function($controller, $url, $page) {
            $twig = $controller->getTwig();
            $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Debug($this->app));
            $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Stopwatch($this->app));
        });
    }

}
