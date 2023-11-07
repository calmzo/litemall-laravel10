<?php

namespace App\Http\Middleware;

use App\Exceptions\BusinessException;
use App\Utils\CodeResponse;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{

    /**
     * 兼容前端header token参数字段
     * @param Request $request
     * @param Closure $next
     * @param string[] ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (!is_null($request->headers->get("X-Litemall-Token"))) {
            $request->headers->set('Authorization','Bearer '.$request->headers->get("X-Litemall-Token"));
        }
        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson() || in_array('wx', $guards)) {
            throw new BusinessException(CodeResponse::UN_LOGIN);
        }
        parent::unauthenticated($request, $guards);
    }
}
