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

        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập.');
        }

        // Lấy club_id từ route
        $clubId = $request->route('club');
        
        // Nếu route không có club parameter (như route index), kiểm tra xem user có ít nhất 1 CLB với role được phép không
        if (!$clubId) {
            $user = \App\Models\User::with(['clubMembers' => function($query) {
                $query->whereIn('status', ['approved', 'active']);
            }])->find($userId);
            
            if (!$user) {
                return redirect()->back()->with('error', 'Không tìm thấy thông tin người dùng.');
        }

            $hasPermission = false;
            foreach ($user->clubMembers as $member) {
                if (in_array($member->position, $roles)) {
                    $hasPermission = true;
                    break;
                }
            }
            
            if (!$hasPermission) {
                return redirect()->back()->with('error', 'Bạn không có quyền truy cập chức năng này. Chỉ trưởng CLB, phó CLB và thủ quỹ mới có thể truy cập.');
            }
            
            return $next($request);
        }

        // Query trực tiếp từ database để đảm bảo data mới nhất
        $clubMember = \App\Models\ClubMember::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'active'])
            ->first();

        if (!$clubMember) {
            return redirect()->back()->with('error', 'Bạn không phải là thành viên của câu lạc bộ này.');
        }

        // Kiểm tra xem user có vai trò được phép không
        if (!in_array($clubMember->position, $roles)) {
            return redirect()->back()->with('error', 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}

