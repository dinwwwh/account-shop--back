<?php

namespace App\Http\Middleware;

use App\Helpers\RequestHelper;
use Closure;
use Illuminate\Http\Request;

class MakeConfigurationOfRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        config(['request.requiredModelRelationships' => RequestHelper::generateRequiredModelRelationships($request)]);
        return $next($request);
    }
}
