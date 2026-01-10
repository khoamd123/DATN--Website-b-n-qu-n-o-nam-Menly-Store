<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClubLeaderMiddleware
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

        // Lấy user_id từ session
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập.');
        }

        // Lấy club_id từ route hoặc từ CLB đầu tiên của user
        $clubId = $request->route('club');
        
        // Lấy position từ database thay vì session để đảm bảo luôn có dữ liệu mới nhất
        try {
            $user = \App\Models\User::find($userId);
            if (!$user) {
                return redirect()->route('simple.login')->with('error', 'Không tìm thấy thông tin người dùng.');
            }
            
            // Nếu không có club_id trong route, lấy CLB đầu tiên của user
            if (!$clubId && $user->clubs->count() > 0) {
                $clubId = $user->clubs->first()->id;
            }
            
            if (!$clubId) {
                return redirect()->back()->with('error', 'Không tìm thấy thông tin câu lạc bộ.');
            }
            
            $userRole = $user->getPositionInClub($clubId);
        } catch (\Exception $e) {
            \Log::error('Error in ClubLeaderMiddleware: ' . $e->getMessage());
            // Fallback: lấy từ session nếu có lỗi
            $clubRoles = session('club_roles', []);
            $userRole = $clubRoles[$clubId] ?? null;
        }

        if ($userRole !== 'leader') {
            return redirect()->back()->with('error', 'Bạn không có quyền truy cập chức năng này. Chỉ trưởng CLB mới có thể thực hiện.');
        }

        return $next($request);
    }
}
