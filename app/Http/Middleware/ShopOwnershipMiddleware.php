<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShopOwnershipMiddleware (Single-Shop Edition)
 *
 * In a single-shop deployment this middleware is a passthrough.
 * The concept of "which shop does this user belong to?" is no longer
 * relevant — there is only one shop in the system.
 */
class ShopOwnershipMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
