<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SimpleLoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        // Nếu đã đăng nhập, redirect về trang tương ứng
        if (session('logged_in') && session('user_id')) {
            if (session('is_admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            $credentials = $request->only('email', 'password');

            // Tìm user bằng email
            try {
                $user = User::where('email', $credentials['email'])->first();
            } catch (\Illuminate\Database\QueryException $e) {
                \Log::error('Database connection error in login: ' . $e->getMessage());
                return back()->withErrors([
                    'email' => 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra MySQL server đã chạy chưa và thử lại sau.',
                ])->onlyInput('email');
            }
            
            if (!$user) {
                return back()->withErrors([
                    'email' => 'Email hoặc mật khẩu không đúng.',
                ])->onlyInput('email');
            }
            
            // Kiểm tra mật khẩu
            if (!Hash::check($credentials['password'], $user->password)) {
                return back()->withErrors([
                    'email' => 'Email hoặc mật khẩu không đúng.',
                ])->onlyInput('email');
            }
            
            // Lấy club roles của user
            $clubRoles = [];
            try {
                $clubMemberships = $user->clubMembers()->where('status', 'active')->get();
                foreach ($clubMemberships as $membership) {
                    $clubRoles[$membership->club_id] = $membership->position;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                // Nếu không lấy được club members, để mảng rỗng
                \Log::warning('Could not fetch club members in login: ' . $e->getMessage());
                $clubRoles = [];
            }

            // Đăng nhập thành công - lưu thông tin user vào session
            session([
                'logged_in' => true,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'student_id' => $user->student_id,
                'is_admin' => $user->is_admin,
                'club_roles' => $clubRoles
            ]);
            
            // Redirect admin to admin panel
            if ($user->is_admin) {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Đăng nhập thành công!');
            }
            
            // Redirect regular users to homepage
            return redirect()->intended(route('home'))->with('success', 'Đăng nhập thành công!');
            
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Có lỗi xảy ra. Vui lòng thử lại.',
            ])->onlyInput('email');
        }
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // Xóa thông tin user khỏi session
        $request->session()->forget(['logged_in', 'user_id', 'user_name', 'user_email', 'student_id', 'is_admin', 'club_roles']);
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('simple.login')->with('success', 'Đã đăng xuất thành công!');
    }
}

