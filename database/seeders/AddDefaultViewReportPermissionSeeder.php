<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;

class AddDefaultViewReportPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Thêm quyền "xem_bao_cao" cho tất cả thành viên CLB hiện tại chưa có quyền này
     */
    public function run(): void
    {
        // Lấy permission "xem_bao_cao"
        $viewReportPermission = Permission::where('name', 'xem_bao_cao')->first();
        
        if (!$viewReportPermission) {
            $this->command->error('Không tìm thấy quyền "xem_bao_cao"');
            return;
        }

        // Lấy tất cả thành viên CLB có status = 'approved' hoặc 'active'
        $clubMembers = DB::table('club_members')
            ->whereIn('status', ['approved', 'active'])
            ->get();

        $addedCount = 0;
        $skippedCount = 0;

        foreach ($clubMembers as $member) {
            // Kiểm tra xem đã có quyền này chưa
            $existingPermission = DB::table('user_permissions_club')
                ->where('user_id', $member->user_id)
                ->where('club_id', $member->club_id)
                ->where('permission_id', $viewReportPermission->id)
                ->first();

            if (!$existingPermission) {
                // Thêm quyền "xem_bao_cao"
                DB::table('user_permissions_club')->insert([
                    'user_id' => $member->user_id,
                    'club_id' => $member->club_id,
                    'permission_id' => $viewReportPermission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $addedCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->command->info("Đã thêm quyền 'xem_bao_cao' cho {$addedCount} thành viên.");
        $this->command->info("Đã bỏ qua {$skippedCount} thành viên (đã có quyền này).");
    }
}















