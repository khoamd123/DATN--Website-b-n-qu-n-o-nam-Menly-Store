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
        ];
        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'address' => $user['address'],
                'avatar' => $user['avatar'],
                'is_admin' => $user['is_admin'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]);
        }
    }
}
