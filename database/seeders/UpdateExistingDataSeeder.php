<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubJoinRequest;

class UpdateExistingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Lấy user admin
        $admin = User::where('is_admin', true)->first();
        
        if (!$admin) {
            $this->command->error('Không tìm thấy admin user!');
            return;
        }

        // Lấy club đầu tiên
        $club = Club::first();
        
        if (!$club) {
            $this->command->error('Không tìm thấy club nào!');
            return;
        }

        // Cập nhật leader_id cho club
        $club->update(['leader_id' => $admin->id]);

        // Lấy các user không phải admin
        $users = User::where('is_admin', false)->get();

        if ($users->count() >= 3) {
            // User đầu tiên làm leader
            $leader = $users[0];
            ClubMember::firstOrCreate(
                ['user_id' => $leader->id, 'club_id' => $club->id],
                [
                    'role_in_club' => 'chunhiem',
                    'position' => 'leader',
                    'status' => 'approved',
                    'joined_at' => now()
                ]
            );

            // User thứ 2 làm officer
            $officer = $users[1];
            ClubMember::firstOrCreate(
                ['user_id' => $officer->id, 'club_id' => $club->id],
                [
                    'role_in_club' => 'phonhiem',
                    'position' => 'officer',
                    'status' => 'approved',
                    'joined_at' => now()
                ]
            );

            // User thứ 3 làm member
            $member = $users[2];
            ClubMember::firstOrCreate(
                ['user_id' => $member->id, 'club_id' => $club->id],
                [
                    'role_in_club' => 'thanhvien',
                    'position' => 'member',
                    'status' => 'approved',
                    'joined_at' => now()
                ]
            );

            // Các user còn lại tạo đơn xin gia nhập
            for ($i = 3; $i < $users->count(); $i++) {
                $user = $users[$i];
                ClubJoinRequest::firstOrCreate(
                    ['user_id' => $user->id, 'club_id' => $club->id],
                    [
                        'message' => 'Tôi muốn tham gia CLB để học hỏi và phát triển kỹ năng.',
                        'status' => 'pending'
                    ]
                );
            }
        }

        $this->command->info('Đã cập nhật dữ liệu thành công!');
        $this->command->info('Admin: ' . $admin->email);
        $this->command->info('Club: ' . $club->name);
        $this->command->info('Members: ' . ClubMember::count());
        $this->command->info('Join requests: ' . ClubJoinRequest::count());
    }
}
