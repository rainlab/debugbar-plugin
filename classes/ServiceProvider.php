<?php namespace RainLab\Debugbar\Classes;

use Illuminate\Contracts\Http\Kernel;
use Barryvdh\Debugbar\ServiceProvider as BaseServiceProvider;
use Barryvdh\Debugbar\Middleware\InjectDebugbar as InjectDebugbarBase;
use RainLab\Debugbar\Middleware\InjectDebugbar;

/**
 * ServiceProvider
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the Debugbar Middleware
     *
     * @param  string $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $kernel = $this->app[Kernel::class];

        // Prevent double up, base Service Provider has been explicitly loaded
        if (!$kernel->hasMiddleware(InjectDebugbarBase::class)) {
            $kernel->pushMiddleware(InjectDebugbar::class);
        }
    }
}
