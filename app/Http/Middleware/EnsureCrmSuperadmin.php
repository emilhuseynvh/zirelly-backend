<?php

namespace App\Http\Middleware;

use App\Models\CrmUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCrmSuperadmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof CrmUser || ! $user->isSuperadmin()) {
            abort(403, 'Bu əməliyyatı yalnız superadmin edə bilər.');
        }

        return $next($request);
    }
}
