<?php

namespace App\Http\Middleware;

use App\Models\CrmUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCrmSection
{
    public function handle(Request $request, Closure $next, string $section): Response
    {
        $user = $request->user();

        if (! $user instanceof CrmUser || ! $user->canAccess($section)) {
            abort(403, 'Bu bölməyə icazəniz yoxdur.');
        }

        return $next($request);
    }
}
