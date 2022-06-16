<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserType
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param $type
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next, $type)
    {
        if (User::TYPE_SLUGES[auth()->user()->{'type'}] !== $type) {
            abort(404);
        }

        return $next($request);
    }
}
