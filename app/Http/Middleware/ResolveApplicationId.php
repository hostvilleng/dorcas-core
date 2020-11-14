<?php

namespace App\Http\Middleware;


use Closure;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResolveApplicationId
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('client_id') && !is_numeric($request->input('client_id'))) {
            # we have a hashid'ed client id
            list($actual) = app(Hashids::class)->decode($request->input('client_id'));
            # we decode it
            if ($request->query->has('client_id')) {
                # adjust it in the get parameters
                $request->query->set('client_id', $actual);
            } else {
                $request->request->set('client_id', $actual);
            }
        }
        return $next($request);
    }
}