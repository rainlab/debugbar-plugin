<?php namespace RainLab\Debugbar\Middleware;

use Error;
use Config;
use Request;
use Closure;
use Exception;
use BackendAuth;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Barryvdh\Debugbar\Middleware\InjectDebugbar as BaseMiddleware;

class InjectDebugbar extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->debugbar->isEnabled() || $this->inExceptArray($request)) {
            return $next($request);
        }

        $this->debugbar->boot();

        try {
            /** @var \Illuminate\Http\Response $response */
            $response = $next($request);
        }
        catch (Exception $e) {
            $response = $this->handleException($request, $e);
        }
        catch (Error $error) {
            $e = new FatalThrowableError($error);
            $response = $this->handleException($request, $e);
        }

        $user = BackendAuth::getUser();

        if ((!$user || !$user->hasAccess('rainlab.debugbar.access_stored_requests')) &&
            !Config::get('rainlab.debugbar::store_all_requests', false)) {
            // Disable stored requests
            // Note: this will completely disable storing requests from any users
            // without the required permission. If that functionality is desired again
            // in the future then we can look at overriding the OpenHandler controller
            $this->debugbar->setStorage(null);
        }

        // Modify the response to add the Debugbar if allowed
        if (
            ($user && $user->hasAccess('rainlab.debugbar.access_debugbar')) ||
            Config::get('rainlab.debugbar::allow_public_access', false)
        ) {
            $this->debugbar->modifyResponse($request, $response);
        }

        return $response;
    }
}
