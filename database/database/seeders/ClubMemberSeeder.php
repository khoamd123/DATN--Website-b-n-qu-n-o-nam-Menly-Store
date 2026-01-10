<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\User;
use Carbon\Carbon;

class ClubMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clubs = Club::all(); // 10 CLB
        $users = User::all(); // 10 user

        foreach ($clubs as $club) {
    $ownerId = $club->owner_id;

    if (!$ownerId) continue;

    // Thêm chủ nhiệm
    ClubMember::create([
        'club_id' => $club->id,
        'user_id' => $ownerId,
        'role_in_club' => 'chunhiem',
        'status' => 'approved',
        'joined_at' => now(),
        'left_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Thêm 3–5 thành viên còn lại
    $members = $users->where('id', '!=', $ownerId)
                     ->shuffle()
                     ->take(rand(3,5));

    foreach ($members as $member) {
        ClubMember::create([
            'club_id' => $club->id,
            'user_id' => $member->id,
            'role_in_club' => 'thanhvien',
            'status' => 'approved',
            'joined_at' => now(),
            'left_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

    }
}
