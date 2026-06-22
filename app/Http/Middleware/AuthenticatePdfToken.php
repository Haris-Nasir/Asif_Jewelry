<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticatePdfToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->query('token') ?: $request->bearerToken();

        if (!$token) {
            abort(403, 'PDF access requires authentication.');
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable) {
            abort(403, 'Invalid or expired PDF access token.');
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            abort(403, 'PDF access token has expired.');
        }

        auth()->setUser($accessToken->tokenable);

        return $next($request);
    }
}
