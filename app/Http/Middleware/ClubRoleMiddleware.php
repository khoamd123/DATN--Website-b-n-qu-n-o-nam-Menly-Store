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

        // Kiểm tra quyền admin (admin có quyền truy cập mọi CLB)
        if (session('is_admin')) {
            return $next($request);
        }

        // Lấy club_id từ route hoặc từ query parameter
        $clubId = $request->route('club') ?? $request->input('club');
        
        // Nếu vẫn không có club_id, thử lấy từ user's first club
        if (!$clubId) {
            $userId = session('user_id');
            if ($userId) {
                try {
                    $user = \App\Models\User::find($userId);
                    if ($user && $user->clubs->count() > 0) {
                        $clubId = $user->clubs->first()->id;
                    }
                } catch (\Exception $e) {
                    // Ignore error
                }
            }
        }

        if (!$clubId) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin câu lạc bộ.');
        }

        // Lấy vai trò của user trong CLB này từ database (đảm bảo dữ liệu mới nhất)
        try {
            $userId = session('user_id');
            $user = \App\Models\User::find($userId);
            
            if ($user) {
                $userRole = $user->getPositionInClub($clubId);
            } else {
                // Fallback: lấy từ session
                $clubRoles = session('club_roles', []);
                $userRole = $clubRoles[$clubId] ?? null;
            }
        } catch (\Exception $e) {
            // Fallback: lấy từ session nếu có lỗi
            $clubRoles = session('club_roles', []);
            $userRole = $clubRoles[$clubId] ?? null;
        }

        // Kiểm tra xem user có vai trò được phép không
        if (!$userRole || !in_array($userRole, $roles)) {
            return redirect()->back()->with('error', 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}

