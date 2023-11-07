<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenConvert
{

    /**
     * 兼容前端header token参数字段
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!is_null($request->headers->get("X-Litemall-Token"))) {
            $request->headers->set('Authorization','Bearer '.$request->headers->get("X-Litemall-Token"));
        }
        return $next($request);
    }
}
