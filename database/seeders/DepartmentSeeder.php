<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Club;
use App\Models\Department;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $departments = [];

        $departmentTemplates = [
            ['name' => 'Ban tổ chức', 'description' => 'Quản lý sự kiện, hoạt động của CLB'],
            ['name' => 'Ban truyền thông', 'description' => 'Quản lý thông tin, bài viết và mạng xã hội'],
            ['name' => 'Ban tài chính', 'description' => 'Quản lý quỹ, chi tiêu, thu chi CLB'],
            ['name' => 'Ban học thuật', 'description' => 'Hỗ trợ học tập và nghiên cứu'],
        ];

        $clubs = Club::all();

        foreach ($clubs as $club) {
            // Chọn 1-3 phòng ban ngẫu nhiên cho mỗi club
            $chosenDepartments = collect($departmentTemplates)->shuffle()->take(rand(1,3));

            foreach ($chosenDepartments as $dept) {
                $departments[] = [
                    'club_id' => $club->id,
                    'name' => $dept['name'],
                    'description' => $dept['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];
            }
        }

        // Chèn tất cả vào DB
        Department::insert($departments);
    }
}
