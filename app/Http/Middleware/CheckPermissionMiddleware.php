<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Kiểm tra đăng nhập
        if (!session('logged_in')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập.');
        }

        $userId = session('user_id');
        try {
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                return redirect()->route('simple.login')->with('error', 'Không tìm thấy thông tin người dùng.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Xử lý lỗi kết nối database
            \Log::error('Database connection error in CheckPermissionMiddleware: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.'
                ], 503);
            }
            return redirect()->route('simple.login')->with('error', 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.');
        } catch (\Exception $e) {
            \Log::error('Error in CheckPermissionMiddleware: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã xảy ra lỗi. Vui lòng thử lại sau.'
                ], 500);
            }
            return redirect()->route('simple.login')->with('error', 'Đã xảy ra lỗi. Vui lòng thử lại sau.');
        }

        // Lấy club_id từ route hoặc request
        $clubId = $request->route('club') ?? $request->input('club_id');
        
        // Kiểm tra quyền
        if (!$user->hasPermission($permission, $clubId)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Bạn không có quyền thực hiện hành động này. Yêu cầu quyền: {$permission}",
                    'required_permission' => $permission
                ], 403);
            }
            
            return redirect()->back()->with('error', "Bạn không có quyền thực hiện hành động này. Yêu cầu quyền: {$permission}");
        }

        return $next($request);
    }
}

