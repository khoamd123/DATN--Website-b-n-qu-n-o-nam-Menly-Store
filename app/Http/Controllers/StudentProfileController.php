<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StudentProfileController extends Controller
{
    /**
     * Show the user's profile.
     */
    public function index()
    {
        // Tạm thời dùng user cứng, sẽ thay bằng Auth::user() sau khi có auth hoàn chỉnh
        $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');
        }
        return view('student.profile.index', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit()
    {
        // Tạm thời dùng user cứng, sẽ thay bằng Auth::user() sau khi có auth hoàn chỉnh
        $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');
        }
        return view('student.profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        // Tạm thời dùng user cứng
        // In a real app, this would be Auth::user()
        $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $request->name; // Email is not updated here to prevent login issues
        $user->phone = $request->phone;
        $user->address = $request->address;

        // Xử lý upload avatar nếu có
        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu có
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }

            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . $user->id . '.' . $avatar->getClientOriginalExtension();
            $avatarDir = public_path('uploads/avatars');

            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($avatarDir)) {
                mkdir($avatarDir, 0755, true);
            }

            $avatar->move($avatarDir, $avatarName);
            $user->avatar = 'uploads/avatars/' . $avatarName;
        }

        $user->save();

        return redirect()->route('student.profile.index')->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }
}