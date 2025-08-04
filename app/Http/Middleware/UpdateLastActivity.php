<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class UpdateLastActivity
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $now = now();
        $user->last_activity = $now;
        $user->save();

        return $next($request);
    }
}
