<?php

namespace Pterodactyl\Http\Middleware\Api;

use Closure;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ApiSubstituteBindings extends SubstituteBindings
{
    /**
     * Perform substitution of route parameters without triggering
     * a 404 error if a model is not found.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = $request->route();

        $this->router->substituteBindings($route);

        // Attempt to resolve bindings for this route. If one of the models
        // cannot be resolved do not immediately return a 404 error. Set a request
        // attribute that can be checked in the base API request class to only
        // trigger a 404 after validating that the API key making the request is valid
        // and even has permission to access the requested resource.
        try {
            $this->router->substituteImplicitBindings($route);
        } catch (ModelNotFoundException $exception) {
            $request->attributes->set('is_missing_model', true);
        }

        return $next($request);
    }
}
