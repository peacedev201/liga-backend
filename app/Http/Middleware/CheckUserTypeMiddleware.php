<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Auth\Access\AuthorizationException;

class CheckUserTypeMiddleware
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$types)
    {
        $this->checkUserType($request, $types);

        return $next($request);
    }

    protected function checkUserType($request, array $types)
    {
        if (empty($types)) {
            $types = [null];
        }

        foreach ($types as $type) {
            $user = $this->auth->user();
            if ($user->profileable_type == $type) {
                return;
            }
        }

        $this->unauthenticated($request, $types);
    }

    protected function unauthenticated($request, array $types)
    {
        throw new AuthorizationException();
    }
}
