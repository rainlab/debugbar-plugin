<?php namespace RainLab\Debugbar;

use App;
use Event;
use Config;
use BackendAuth;
use Backend\Models\UserRole;
use System\Classes\PluginBase;
use System\Classes\CombineAssets;
use Illuminate\Foundation\AliasLoader;

/**
 * Debugbar Plugin Information File
 *
 * TODO:
 * - Fix styling by scoping a html reset to phpdebugbar-openhandler and phpdebugbar
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
            'name'        => 'rainlab.debugbar::lang.plugin.name',
            'description' => 'rainlab.debugbar::lang.plugin.description',
            'author'      => 'RainLab',
            'icon'        => 'icon-bug',
            'homepage'    => 'https://github.com/rainlab/debugbar-plugin'
        ];
    }

    /**
     * Register service provider, Twig extensions, and alias facade.
     */
    public function boot()
    {
        // Configure the debugbar
        Config::set('debugbar', Config::get('rainlab.debugbar::config'));

        // Service provider
        App::register(\RainLab\Debugbar\Classes\ServiceProvider::class);

        // Register alias
        $alias = AliasLoader::getInstance();
        $alias->alias('Debugbar', '\Barryvdh\Debugbar\Facade');

        // Register middleware
        if (Config::get('app.debugAjax', false)) {
            $this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware('\RainLab\Debugbar\Middleware\InterpretsAjaxExceptions');
        }

        // Add styling
        $addResources = function ($controller) {
            $debugBar = $this->app->make('Barryvdh\Debugbar\LaravelDebugbar');
            if ($debugBar->isEnabled()) {
                $controller->addCss(url(Config::get('cms.pluginsPath', '/plugins') . '/rainlab/debugbar/assets/css/debugbar.css'));
            }
        };
        Event::listen('backend.page.beforeDisplay', $addResources, PHP_INT_MAX);
        Event::listen('cms.page.beforeDisplay', $addResources, PHP_INT_MAX);

        Event::listen('cms.page.beforeDisplay', function ($controller, $url, $page) {
            // Twig extensions
            $twig = $controller->getTwig();
            if (!$twig->hasExtension(\Barryvdh\Debugbar\Twig\Extension\Debug::class)) {
                $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Debug($this->app));
                $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Stopwatch($this->app));
            }
        });
    }

    /**
     * Register the
     */
    public function register()
    {
        /*
         * Register asset bundles
         */
        CombineAssets::registerCallback(function ($combiner) {
            $combiner->registerBundle('$/rainlab/debugbar/assets/css/debugbar.less');
        });
    }

    /**
     * Register the permissions used by the plugin
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'rainlab.debugbar.access_debugbar' => [
                'tab' => 'rainlab.debugbar::lang.plugin.name',
                'label' => 'rainlab.debugbar::lang.plugin.access_debugbar',
                'roles' => UserRole::CODE_DEVELOPER,
            ],
            'rainlab.debugbar.access_stored_requests' => [
                'tab' => 'rainlab.debugbar::lang.plugin.name',
                'label' => 'rainlab.debugbar::lang.plugin.access_stored_requests',
                'roles' => UserRole::CODE_DEVELOPER,
            ],
        ];
    }
}
