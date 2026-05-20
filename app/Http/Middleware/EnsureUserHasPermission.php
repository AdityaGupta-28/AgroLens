<?php

namespace App\Http\Middleware;

use App\Enums\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        $perm = Permission::tryFrom($permission);

        if (! $user || ! $perm || ! $user->hasPermission($perm)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
