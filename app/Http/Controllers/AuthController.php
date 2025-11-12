<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
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
            $user = User::where('email', $credentials['email'])->first();
            
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
            $clubMemberships = $user->clubMembers()->where('status', 'active')->get();
            
            foreach ($clubMemberships as $membership) {
                $clubRoles[$membership->club_id] = $membership->position;
            }

            // Đăng nhập thành công - lưu thông tin user vào session (giống SimpleLoginController)
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
            return redirect()->intended(route('home', [], false))->with('success', 'Đăng nhập thành công!');
            
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
        // Xóa thông tin user khỏi session (giống SimpleLoginController)
        $request->session()->forget(['logged_in', 'user_id', 'user_name', 'user_email', 'student_id', 'is_admin', 'club_roles']);
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Đã đăng xuất thành công!');
    }

    /**
     * Show register form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle register request
     */
    public function register(Request $request)
    {
        // Kiểm tra email trường trước
        if (!User::isUniversityEmail($request->email)) {
            return back()->withErrors([
                'email' => 'Chỉ cho phép đăng ký bằng email trường (.edu.vn)',
            ])->withInput();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        // Tự động tạo student_id và name từ email trường
        $studentId = User::generateStudentIdFromEmail($request->email);
        $autoName = User::extractNameFromEmail($request->email);

        $user = User::create([
            'name' => $request->name ?: $autoName, // Ưu tiên tên người dùng nhập, nếu không có thì dùng tên tự động
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'student_id' => $studentId,
            'is_admin' => false,
            'role' => 'user',
        ]);

        // Lấy club roles của user (mới đăng ký nên sẽ rỗng)
        $clubRoles = [];

        // Lưu thông tin đăng nhập vào session (giống như login)
        session([
            'logged_in' => true,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'student_id' => $user->student_id,
            'is_admin' => $user->is_admin,
            'club_roles' => $clubRoles
        ]);

        return redirect()->intended(route('home', [], false))->with('success', 'Đăng ký thành công! Chào mừng bạn đến với UniClubs.');
    }
}
