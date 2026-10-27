<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->is_banned || $request->is('logout')) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Šis konts ir bloķēts. Sazinies ar atbalsta dienestu.',
            ], 403);
        }

        return redirect('/login')->withErrors([
            'email' => 'Šis konts ir bloķēts. Sazinies ar atbalsta dienestu.',
        ]);
    }
}
