<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isAdmin()) {
            return response()->json([
                'message' => 'Недостаточно прав для выполнения этого действия.',
            ], 403);
        }

        return $next($request);
    }
}
