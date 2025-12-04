<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
<<<<<<< HEAD
            ['name' => 'Nguyen Van A', 'email' => 'nguyenvana@example.com', 'phone' => '0912345678', 'address' => 'Hà Nội', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => true,],
            ['name' => 'Tran Thi B', 'email' => 'tranthib@example.com', 'phone' => '0923456789', 'address' => 'TP. Hồ Chí Minh', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false,],
            ['name' => 'Le Van C', 'email' => 'levanc@example.com', 'phone' => '0934567890', 'address' => 'Đà Nẵng', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false,],
            ['name' => 'Pham Thi D', 'email' => 'phamthid@example.com', 'phone' => '0945678901', 'address' => 'Hải Phòng', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false,],
            ['name' => 'Hoang Van E', 'email' => 'hoangvane@example.com', 'phone' => '0956789012', 'address' => 'Cần Thơ', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false,],
            ['name' => 'Nguyen Thi F', 'email' => 'nguyenthif@example.com', 'phone' => '0967890123', 'address' => 'Huế', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false,],
            ['name' => 'Tran Van G', 'email' => 'tranvang@example.com', 'phone' => '0978901234', 'address' => 'Quảng Ninh', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false,],
            ['name' => 'Le Thi H', 'email' => 'lethih@example.com', 'phone' => '0989012345', 'address' => 'Bình Dương', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false,],
            ['name' => 'Pham Van I', 'email' => 'phamvani@example.com', 'phone' => '0990123456', 'address' => 'Nha Trang', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false,],
            ['name' => 'Do Thi J', 'email' => 'dothij@example.com', 'phone' => '0901234567', 'address' => 'Hải Dương', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false,],
=======
            ['name' => 'Nguyen Van A', 'email' => 'admin@university.edu.vn', 'phone' => '0912345678', 'address' => 'Hà Nội', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => true, 'role' => 'admin', 'student_id' => '2024001'],
            ['name' => 'Tran Thi B', 'email' => 'tranthib@university.edu.vn', 'phone' => '0923456789', 'address' => 'TP. Hồ Chí Minh', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false, 'role' => 'club_manager', 'student_id' => '2024002'],
            ['name' => 'Le Van C', 'email' => 'levanc@university.edu.vn', 'phone' => '0934567890', 'address' => 'Đà Nẵng', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false, 'role' => 'executive_board', 'student_id' => '2024003'],
            ['name' => 'Pham Thi D', 'email' => 'phamthid@university.edu.vn', 'phone' => '0945678901', 'address' => 'Hải Phòng', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false, 'role' => 'user', 'student_id' => '2024004'],
            ['name' => 'Hoang Van E', 'email' => 'hoangvane@university.edu.vn', 'phone' => '0956789012', 'address' => 'Cần Thơ', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false, 'role' => 'user', 'student_id' => '2024005'],
            ['name' => 'Nguyen Thi F', 'email' => 'nguyenthif@university.edu.vn', 'phone' => '0967890123', 'address' => 'Huế', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false, 'role' => 'user', 'student_id' => '2024006'],
            ['name' => 'Tran Van G', 'email' => 'tranvang@university.edu.vn', 'phone' => '0978901234', 'address' => 'Quảng Ninh', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false, 'role' => 'user', 'student_id' => '2024007'],
            ['name' => 'Le Thi H', 'email' => 'lethih@university.edu.vn', 'phone' => '0989012345', 'address' => 'Bình Dương', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false, 'role' => 'user', 'student_id' => '2024008'],
            ['name' => 'Pham Van I', 'email' => 'phamvani@university.edu.vn', 'phone' => '0990123456', 'address' => 'Nha Trang', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false, 'role' => 'user', 'student_id' => '2024009'],
            ['name' => 'Do Thi J', 'email' => 'dothij@university.edu.vn', 'phone' => '0901234567', 'address' => 'Hải Dương', 'avatar' => 'images/avatar/avatar.png', 'is_admin' => false, 'role' => 'user', 'student_id' => '2024010'],
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
        ];
        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'address' => $user['address'],
                'avatar' => $user['avatar'],
                'is_admin' => $user['is_admin'],
<<<<<<< HEAD
=======
                'role' => $user['role'],
                'student_id' => $user['student_id'],
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]);
        }
    }
}
