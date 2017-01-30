<?php namespace Bedard\Debugbar;

use App;
use Event;
use Debugbar;
use BackendAuth;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;

/**
 * Debugbar Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var boolean Determine if this plugin should have elevated privileges.
     */

    public $elevated = true;
    
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
            'icon'        => 'icon-cog',
            'homepage'    => 'https://github.com/scottbedard/oc-debugbar-plugin'
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

        Event::listen('cms.page.beforeDisplay', function($controller, $url, $page)
        {
            // Only show for authenticated backend users
            if (!BackendAuth::check()) {
                Debugbar::disable();
            }

            // Twig extensions
            $twig = $controller->getTwig();
            if(!$twig->hasExtension(\Barryvdh\Debugbar\Twig\Extension\Debug::class)){
                $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Debug($this->app));
                $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Stopwatch($this->app));
            }
        });
    }
}
