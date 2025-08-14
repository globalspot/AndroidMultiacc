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
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || !$this->hasRole($request->user(), $role)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }

    /**
     * Check if user has the required role
     */
    private function hasRole($user, string $role): bool
    {
        switch ($role) {
            case 'admin':
                return $user->isAdmin();
            case 'manager':
                return $user->isManager();
            case 'user':
                return $user->isUser();
            case 'admin_or_manager':
                return $user->isAdminOrManager();
            default:
                return false;
        }
    }
}
