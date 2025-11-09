<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClubPermissionMiddleware
{
    /**
     * Handle an incoming request.
     * Check if user has specific permission for a club
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        // Kiểm tra xem user đã đăng nhập chưa
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập.');
        }

        $user = auth()->user();

        // Admin có tất cả quyền
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Lấy club_id từ route parameter hoặc request
        $clubId = $request->route('club') ?? $request->route('clubId') ?? $request->input('club_id');
        
        // Kiểm tra quyền cho CLB cụ thể
        if ($clubId && $user->hasPermission($permission, $clubId)) {
            return $next($request);
        }

        // Kiểm tra quyền tổng quát (không cần CLB cụ thể)
        if (!$clubId && $user->hasPermission($permission)) {
            return $next($request);
        }

        return redirect()->back()->with('error', 'Bạn không có quyền thực hiện hành động này.');
    }
}
