<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests
{
    
    public function handle(Request $request, Closure $next): Response
    {

        if ($request->is('api/login') && $request->isMethod('post')) {

        }

        return $next($request);
    }
}
