<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClubOfficerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra đăng nhập
        if (!session('logged_in')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập.');
        }

        // Admin có quyền truy cập mọi CLB
        if (session('is_admin')) {
            return $next($request);
        }

        // Lấy club_id từ route
        $clubId = $request->route('club');
        
        if (!$clubId) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin câu lạc bộ.');
        }

        // Kiểm tra user có phải là leader hoặc officer của CLB không
        $clubRoles = session('club_roles', []);
        $userRole = $clubRoles[$clubId] ?? null;

        if (!in_array($userRole, ['leader', 'vice_president', 'officer'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền truy cập chức năng này. Chỉ trưởng CLB, phó CLB và cán sự mới có thể thực hiện.');
        }

        return $next($request);
    }
}

