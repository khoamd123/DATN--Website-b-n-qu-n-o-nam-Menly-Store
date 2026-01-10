<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class StudentProfileController extends Controller
{
    /**
     * Check if user is logged in as student
     */
    private function checkStudentAuth()
    {
        if (!session('user_id') || session('is_admin')) {
            if (session('is_admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập với tài khoản sinh viên.');
        }

        try {
            $user = User::with('clubs')->find(session('user_id'));
            
            if (!$user) {
                session()->forget(['user_id', 'user_name', 'user_email', 'is_admin']);
                return redirect()->route('login')->with('error', 'Phiên đăng nhập đã hết hạn.');
            }

            return $user;
        } catch (\Illuminate\Database\QueryException $e) {
            // Xử lý lỗi kết nối database
            \Log::error('Database connection error in checkStudentAuth: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.');
        } catch (\Exception $e) {
            // Xử lý các lỗi khác
            \Log::error('Error in checkStudentAuth: ' . $e->getMessage());
            session()->forget(['user_id', 'user_name', 'user_email', 'is_admin']);
            return redirect()->route('login')->with('error', 'Đã xảy ra lỗi. Vui lòng đăng nhập lại.');
        }
    }

    /**
     * Show the user's profile.
     */
    public function index()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        return view('student.profile.index', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        return view('student.profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
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