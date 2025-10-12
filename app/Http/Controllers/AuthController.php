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
            
            if ($user && Hash::check($credentials['password'], $user->password)) {
                // Đăng nhập thành công - lưu thông tin user vào session
                $request->session()->put('user_id', $user->id);
                $request->session()->put('user_name', $user->name);
                $request->session()->put('user_email', $user->email);
                $request->session()->put('is_admin', $user->is_admin);
                
                // Redirect admin to admin panel
                if ($user->is_admin) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                
                // Redirect regular users to student dashboard
                return redirect()->intended(route('student.dashboard'));
            }

            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ])->onlyInput('email');
            
        } catch (\Exception $e) {
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
        $request->session()->forget(['user_id', 'user_name', 'user_email', 'is_admin']);
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
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
            'name' => $autoName ?: $request->name, // Ưu tiên tên tự động
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'student_id' => $studentId,
            'is_admin' => false,
            'role' => 'user',
        ]);

        Auth::login($user);

        return redirect()->route('student.dashboard')->with('success', 'Đăng ký thành công! Chào mừng bạn đến với UniClubs.');
    }
}
