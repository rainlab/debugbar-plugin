<?php namespace RainLab\Debugbar;

use App;
use Event;
use Config;
use Cms\Classes\Page;
use Cms\Classes\Layout;
use Cms\Classes\Controller as CmsController;
use Backend\Models\UserRole;
use Backend\Classes\Controller as BackendController;
use System\Classes\CombineAssets;
use System\Classes\PluginBase;
use RainLab\Debugbar\DataCollectors\OctoberBackendCollector;
use RainLab\Debugbar\DataCollectors\OctoberCmsCollector;
use RainLab\Debugbar\DataCollectors\OctoberComponentsCollector;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;

/**
 * Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var boolean Determine if this plugin should have elevated privileges.
     */
    public $elevated = true;

    /**
     * Returns information about this plugin.
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'rainlab.debugbar::lang.plugin.name',
            'description' => 'rainlab.debugbar::lang.plugin.description',
            'author' => 'RainLab',
            'icon' => 'icon-bug',
            'homepage' => 'https://github.com/rainlab/debugbar-plugin'
        ];
    }

    /**
     * boot service provider, Twig extensions, and alias facade.
     */
    public function boot()
    {
        // Transfer config to debugbar namespace
        Config::set('debugbar', Config::get('rainlab.debugbar::config', []));

        // Disabled by config, halt
        if (Config::get('debugbar.enabled') === false) {
            return;
        }

        // Register service provider
        App::register(\RainLab\Debugbar\Classes\ServiceProvider::class);

        // Register alias
        $alias = AliasLoader::getInstance();
        $alias->alias('Debugbar', \Barryvdh\Debugbar\Facades\Debugbar::class);

        // Register middleware
        if (Config::get('app.debug_ajax', Config::get('app.debugAjax', false))) {
            $this->app[HttpKernelContract::class]->pushMiddleware(\RainLab\Debugbar\Middleware\InterpretsAjaxExceptions::class);
        }

        // Custom debugbar collectors and extensions
        $this->registerResourceInjection();

        if (App::runningInBackend()) {
            $this->addBackendCollectors();
        }
        else {
            // Only Twig 2 is supported at this stage
            if (class_exists('\Twig_Extension')) {
                $this->registerCmsTwigExtensions();
            }

            $this->addFrontendCollectors();
        }

        $this->addGlobalCollectors();
    }

    /**
     * register the service provider
     */
    public function register()
    {
        /*
         * Register asset bundles
         */
        CombineAssets::registerCallback(function ($combiner) {
            $combiner->registerBundle('$/rainlab/debugbar/assets/less/debugbar.less');
        });
    }

    /**
     * addGlobalCollectors adds globally available collectors
     */
    public function addGlobalCollectors()
    {
        /** @var \Barryvdh\Debugbar\LaravelDebugbar $debugBar */
        $debugBar = $this->app->make(\Barryvdh\Debugbar\LaravelDebugbar::class);
        $modelsCollector = $this->app->make(\RainLab\Debugbar\DataCollectors\OctoberModelsCollector::class);
        $debugBar->addCollector($modelsCollector);
    }

    /**
     * addFrontendCollectors used by the frontend only
     */
    public function addFrontendCollectors()
    {
        /** @var \Barryvdh\Debugbar\LaravelDebugbar $debugBar */
        $debugBar = $this->app->make(\Barryvdh\Debugbar\LaravelDebugbar::class);

        Event::listen('cms.page.beforeDisplay', function(CmsController $controller, $url, ?Page $page) use ($debugBar) {
            if ($page) {
                $collector = new OctoberCmsCollector($controller, $url, $page);
                if (!$debugBar->hasCollector($collector->getName())) {
                    $debugBar->addCollector($collector);
                }
            }
        });

        Event::listen('cms.page.initComponents', function(CmsController $controller, ?Page $page, ?Layout $layout) use ($debugBar) {
            if ($page) {
                $collector = new OctoberComponentsCollector($controller, $page, $layout);
                if (!$debugBar->hasCollector($collector->getName())) {
                    $debugBar->addCollector($collector);
                }
            }
        });
    }

    /**
     * addBackendCollectors used by the backend only
     */
    public function addBackendCollectors()
    {
        /** @var \Barryvdh\Debugbar\LaravelDebugbar $debugBar */
        $debugBar = $this->app->make(\Barryvdh\Debugbar\LaravelDebugbar::class);

        Event::listen('backend.page.beforeDisplay', function (BackendController $controller, $action, array $params) use ($debugBar) {
            $collector = new OctoberBackendCollector($controller, $action, $params);
            if (!$debugBar->hasCollector($collector->getName())) {
                $debugBar->addCollector($collector);
            }
        });
    }

    /**
     * registerCmsTwigExtensions in the CMS Twig environment
     */
    protected function registerCmsTwigExtensions()
    {
        $profile = new Profile;
        $debugBar = $this->app->make(\Barryvdh\Debugbar\LaravelDebugbar::class);

        Event::listen('cms.page.beforeDisplay', function ($controller, $url, $page) use ($profile, $debugBar) {
            $twig = $controller->getTwig();
            if (!$twig->hasExtension(\Barryvdh\Debugbar\Twig\Extension\Debug::class)) {
                $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Debug($this->app));
                $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Stopwatch($this->app));
            }

            if (!$twig->hasExtension(ProfilerExtension::class)) {
                $twig->addExtension(new ProfilerExtension($profile));
            }
        });

        if (class_exists(\DebugBar\Bridge\NamespacedTwigProfileCollector::class)) {
            $debugBar->addCollector(new \DebugBar\Bridge\NamespacedTwigProfileCollector($profile));
        } else {
            $debugBar->addCollector(new \DebugBar\Bridge\TwigProfileCollector($profile));
        }
    }

    /**
     * registerResourceInjection adds styling to the page
     */
    protected function registerResourceInjection()
    {
        // Add styling
        $addResources = function($controller) {
            $debugBar = $this->app->make(\Barryvdh\Debugbar\LaravelDebugbar::class);
            if ($debugBar->isEnabled()) {
                $controller->addCss('/plugins/rainlab/debugbar/assets/css/debugbar.css');
            }
        };

        Event::listen('backend.page.beforeDisplay', $addResources, PHP_INT_MAX);

        Event::listen('cms.page.beforeDisplay', $addResources, PHP_INT_MAX);
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
