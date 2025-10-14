<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\ClubMember;
use App\Models\DepartmentMember;
use Carbon\Carbon;

class DepartmentMemberSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $departmentMembers = [];

        $departments = Department::all();

        foreach ($departments as $department) {
            $clubMembers = ClubMember::where('club_id', $department->club_id)
                                     ->pluck('user_id')
                                     ->shuffle();

            if ($clubMembers->isEmpty()) continue;

            // 1. Thêm trưởng phòng (first member)
            $departmentMembers[] = [
                'department_id' => $department->id,
                'user_id' => $clubMembers->first(),
                'role_in_department' => 'truongphong',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];

            // 2. Thêm 2–5 thành viên còn lại (không trùng trưởng phòng)
            $members = $clubMembers->slice(1, rand(2,5));
            foreach ($members as $user_id) {
                $departmentMembers[] = [
                    'department_id' => $department->id,
                    'user_id' => $user_id,
                    'role_in_department' => 'thanhvien',
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];
            }
        }

        // Chèn dữ liệu vào DB
        DepartmentMember::insert($departmentMembers);
    }
}
