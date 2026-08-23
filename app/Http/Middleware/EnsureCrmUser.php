<?php

namespace App\Http\Middleware;

use App\Models\CrmUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCrmUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof CrmUser || ! $user->is_active) {
            abort(403, 'CRM girişi üçün icazəniz yoxdur.');
        }

        return $next($request);
    }
}
