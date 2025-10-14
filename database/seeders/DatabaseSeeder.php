<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Club;
use App\Models\ClubMember;
use App\Models\EventComment;
use App\Models\Field;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            FieldSeeder::class,
            ClubSeeder::class,
            ClubMemberSeeder::class,
            EventSeeder::class,
            EventCommentSeeder::class,
            PostSeeder::class,
            PostCommentSeeder::class,
            EventRegistrationSeeder::class,
            EventMemberEvaluationSeeder::class,
            DepartmentSeeder::class,
            DepartmentMemberSeeder::class,
            EventLogSeeder::class,
            NotificationSeeder::class,
            NotificationTargetSeeder::class,
            NotificationReadSeeder::class,
            PermissionSeeder::class,
            UserPermissionClubSeeder::class,
        ]);
        
    }
}
