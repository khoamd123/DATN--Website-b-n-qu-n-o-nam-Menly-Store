<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SimpleLoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('simple-login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        
        if ($user && Hash::check($request->password, $user->password)) {
            // Lấy club roles của user (bao gồm cả approved và active)
            $clubRoles = [];
            $clubMemberships = $user->clubMembers()->whereIn('status', ['approved', 'active'])->get();
            
            foreach ($clubMemberships as $membership) {
                $clubRoles[$membership->club_id] = $membership->position;
            }

            // Lưu thông tin đăng nhập vào session
            session([
                'logged_in' => true,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'student_id' => $user->student_id,
                'is_admin' => $user->is_admin,
                'club_roles' => $clubRoles
            ]);

            if ($user->is_admin) {
                return redirect('/admin')->with('success', 'Đăng nhập thành công!');
            } else {
                return redirect('/')->with('success', 'Đăng nhập thành công!');
            }
        }

        return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])->withInput();
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        session()->forget(['logged_in', 'user_id', 'user_name', 'user_email', 'student_id', 'is_admin', 'club_roles']);
        return redirect()->route('simple.login')->with('success', 'Đã đăng xuất thành công!');
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn()
    {
        return session('logged_in', false);
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin()
    {
        return session('is_admin', false);
    }
}
