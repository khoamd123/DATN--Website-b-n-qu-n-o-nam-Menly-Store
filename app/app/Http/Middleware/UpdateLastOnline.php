<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateLastOnline
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Cập nhật thời gian online nếu user đã đăng nhập (an toàn khi cột chưa migrate)
        if (session('logged_in') && session('user_id')) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'last_online')) {
                    \App\Models\User::where('id', session('user_id'))
                        ->update(['last_online' => now()]);
                }
            } catch (\Throwable $e) {
                // Bỏ qua để không chặn request nếu DB chưa sẵn sàng
            }
        }
        
        return $response;
    }
}
