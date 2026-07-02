<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopOwnershipMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Ensure shop owners and staff are assigned to a shop
        if ($user->isShopOwner() && !$user->shop_id) {
            abort(403, 'Shop owner must be assigned to a shop.');
        }
        
        if ($user->isStaff() && !$user->shop_id) {
            abort(403, 'Staff member must be assigned to a shop.');
        }
        
        return $next($request);
    }
}