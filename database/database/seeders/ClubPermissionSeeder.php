<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubJoinRequest;
use Illuminate\Support\Facades\Hash;

class ClubPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tạo admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@university.edu.vn'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'student_id' => 'ADMIN001',
                'is_admin' => true,
                'role' => 'admin'
            ]
        );

        // Tạo club leader
        $leader = User::firstOrCreate(
            ['email' => 'leader@university.edu.vn'],
            [
                'name' => 'Nguyễn Văn Lãnh đạo',
                'password' => Hash::make('password'),
                'student_id' => '2024001',
                'is_admin' => false,
                'role' => 'user'
            ]
        );

        // Tạo officer
        $officer = User::firstOrCreate(
            ['email' => 'officer@university.edu.vn'],
            [
                'name' => 'Trần Thị Cán sự',
                'password' => Hash::make('password'),
                'student_id' => '2024002',
                'is_admin' => false,
                'role' => 'user'
            ]
        );

        // Tạo member
        $member = User::firstOrCreate(
            ['email' => 'member@university.edu.vn'],
            [
                'name' => 'Lê Văn Thành viên',
                'password' => Hash::make('password'),
                'student_id' => '2024003',
                'is_admin' => false,
                'role' => 'user'
            ]
        );

        // Tạo regular user
        $user = User::firstOrCreate(
            ['email' => 'user@university.edu.vn'],
            [
                'name' => 'Phạm Thị Sinh viên',
                'password' => Hash::make('password'),
                'student_id' => '2024004',
                'is_admin' => false,
                'role' => 'user'
            ]
        );

        // Tạo field trước
        $field = \App\Models\Field::firstOrCreate(
            ['name' => 'Công nghệ thông tin'],
            [
                'slug' => 'cong-nghe-thong-tin',
                'description' => 'Lĩnh vực công nghệ thông tin và lập trình'
            ]
        );

        // Tạo CLB
        $club = Club::firstOrCreate(
            ['name' => 'CLB Lập trình'],
            [
                'slug' => 'clb-lap-trinh',
                'description' => 'Câu lạc bộ lập trình và phát triển phần mềm',
                'logo' => '/images/clubs/default-logo.png',
                'field_id' => $field->id,
                'owner_id' => $admin->id,
                'leader_id' => $leader->id,
                'status' => 'active'
            ]
        );

        // Thêm leader vào CLB
        ClubMember::firstOrCreate(
            ['user_id' => $leader->id, 'club_id' => $club->id],
            [
                'position' => 'leader',
                'status' => 'active',
                'joined_at' => now()
            ]
        );

        // Thêm officer vào CLB
        ClubMember::firstOrCreate(
            ['user_id' => $officer->id, 'club_id' => $club->id],
            [
                'position' => 'officer',
                'status' => 'active',
                'joined_at' => now()
            ]
        );

        // Thêm member vào CLB
        ClubMember::firstOrCreate(
            ['user_id' => $member->id, 'club_id' => $club->id],
            [
                'position' => 'member',
                'status' => 'active',
                'joined_at' => now()
            ]
        );

        // Tạo đơn xin gia nhập
        ClubJoinRequest::firstOrCreate(
            ['user_id' => $user->id, 'club_id' => $club->id],
            [
                'message' => 'Tôi muốn tham gia CLB để học hỏi về lập trình.',
                'status' => 'pending'
            ]
        );

        $this->command->info('Club permission seeder completed successfully!');
        $this->command->info('Test accounts created:');
        $this->command->info('Admin: admin@university.edu.vn / password');
        $this->command->info('Leader: leader@university.edu.vn / password');
        $this->command->info('Officer: officer@university.edu.vn / password');
        $this->command->info('Member: member@university.edu.vn / password');
        $this->command->info('User: user@university.edu.vn / password');
    }
}
