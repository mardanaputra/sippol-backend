<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak terotentikasi.'
            ], 401);
        }

        if (in_array($user->role, ['super_admin', 'admin'])) {
            return $next($request);
        }

        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak. Anda tidak memiliki hak akses untuk fitur ini.'
        ], 403);
    }
}
