<?php

namespace App\Http\Middleware;

use App\Helpers\Transformer;
use Closure;

class Guest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string    $guard
     * 
     * @return mixed
     */
    public function handle($request, Closure $next, string $guard = null)
    {
        if (!auth()->guard($guard)->guest()) {
            return Transformer::fail('Only for guest user.', null, 403);
        }
        
        return $next($request);
    }
}
