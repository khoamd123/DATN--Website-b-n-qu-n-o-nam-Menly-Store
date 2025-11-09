<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra xem user đã đăng nhập chưa
        if (!$request->session()->has('user_id')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập admin panel.');
        }

        // Kiểm tra xem user có phải admin không
        if (!$request->session()->get('is_admin')) {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang admin.');
        }

        return $next($request);
    }
}

