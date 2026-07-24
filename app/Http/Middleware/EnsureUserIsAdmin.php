<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hanya user dengan `is_admin = true` yang boleh membuka panel admin.
 * User biasa yang memaksa masuk dikembalikan ke beranda.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_admin) {
            abort(403, 'Akun Anda tidak memiliki akses ke panel admin.');
        }

        return $next($request);
    }
}
