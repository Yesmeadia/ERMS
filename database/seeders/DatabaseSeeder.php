<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Examination;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $schoolAdminRole = Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'invigilator', 'guard_name' => 'web']);

        // 2. Create Super Admin User
        $superAdmin = User::updateOrCreate(
            ['email' => 'anfasanukaloor@gmail.com'],
            [
                'name' => 'Board Super Admin',
                'password' => bcrypt('App@Kalo9400#'),
                'school_id' => null,
            ]
        );
        $superAdmin->assignRole($superAdminRole);



        // 5. Create Default Examination Sessions
        $exams = [
            [
                'name' => 'YES GENIUS EXAMINATION SEASON-4',
                'academic_year' => '2025-2026',
                'registration_start_date' => '2026-09-01',
                'registration_end_date' => '2026-11-30',
                'hall_ticket_release_date' => '2027-02-15',
                'status' => 'Registration Started',
            ],
        ];

        foreach ($exams as $exam) {
            Examination::updateOrCreate(['name' => $exam['name']], $exam);
        }
    }
}
