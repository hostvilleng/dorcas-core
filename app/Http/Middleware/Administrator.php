<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;

class Administrator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function handle($request, Closure $next)
    {
        if (empty($request->user())) {
            throw new AuthenticationException();
        }
        if (!str_contains($request->user()->email, ['dorcas.ng'])) {
            throw new AuthorizationException('You do not have access to this feature.');
        }
        return $next($request);
    }
}