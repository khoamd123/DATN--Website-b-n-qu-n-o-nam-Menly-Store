<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClubRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Kiểm tra đăng nhập
        if (!session('logged_in')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập.');
        }

        // Lấy club_id từ route
        $clubId = $request->route('club');
        
        if (!$clubId) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin câu lạc bộ.');
        }

        // Kiểm tra quyền admin (admin có quyền truy cập mọi CLB)
        if (session('is_admin')) {
            return $next($request);
        }

        // Lấy vai trò của user trong CLB này
        $clubRoles = session('club_roles', []);
        $userRole = $clubRoles[$clubId] ?? null;

        // Kiểm tra xem user có vai trò được phép không
        if (!$userRole || !in_array($userRole, $roles)) {
            return redirect()->back()->with('error', 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}

