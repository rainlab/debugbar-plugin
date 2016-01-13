<?php namespace Bedard\Debugbar\Middleware;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Http\Response;
use October\Rain\Exception\AjaxException;

class Debugbar implements Middleware
{

    /**
     * The Laravel Application
     *
     * @var Application
     */
    protected $app;

    /**
     * Create a new middleware instance.
     *
     * @param  Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var \Barryvdh\Debugbar\LaravelDebugbar $debugbar */
        $debugbar = $this->app['debugbar'];
        try {
            /** @var \Illuminate\Http\Response $response */
            $response = $next($request);
            return $debugbar->modifyResponse($request, $response);
        } catch (\Exception $ex) {
            if (!\Request::ajax()) {
                throw $ex;
            }
            $debugbar->addException($ex);
            $message = $ex instanceof AjaxException
                ? $ex->getContents() : \October\Rain\Exception\ErrorHandler::getDetailedMessage($ex);

            return \Response::make($message, 500, $debugbar->getDataAsHeaders());
        }

    }
}
