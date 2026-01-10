<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
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
            
            // Lấy club roles của user (bao gồm cả approved và active)
            $clubRoles = [];
            $clubMemberships = $user->clubMembers()->whereIn('status', ['approved', 'active'])->get();
            
            foreach ($clubMemberships as $membership) {
                $clubRoles[$membership->club_id] = $membership->position;
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
        
        return redirect()->route('login')->with('success', 'Đã đăng xuất thành công!');
    }

    /**
     * Show register form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle register request
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6|confirmed',
                'student_id' => 'nullable|string|unique:users,student_id',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'student_id' => $request->student_id,
                'is_admin' => false,
            ]);

            // Auto login after registration
            session([
                'logged_in' => true,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'student_id' => $user->student_id,
                'is_admin' => false,
                'club_roles' => []
            ]);

            return redirect()->route('home')->with('success', 'Đăng ký thành công!');
            
        } catch (\Exception $e) {
            \Log::error('Register error: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Có lỗi xảy ra. Vui lòng thử lại.',
            ])->withInput();
        }
    }

    /**
     * Quick login for testing (remove in production)
     */
    public function quickLoginStudent()
    {
        $user = User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found');
        }
        
        // Set session
        session_start();
        session([
            'logged_in' => true,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'student_id' => $user->student_id,
            'is_admin' => false,
            'club_roles' => []
        ]);
        
        // Update club roles
        if ($user->clubs->count() > 0) {
            $clubRoles = [];
            foreach ($user->clubs as $club) {
                $position = $user->getPositionInClub($club->id);
                $clubRoles[$club->id] = $position;
            }
            session(['club_roles' => $clubRoles]);
        }
        
        // Force save session
        session()->save();
        
        return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
    }
}



