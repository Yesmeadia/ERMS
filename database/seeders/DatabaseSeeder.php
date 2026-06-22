<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\School;
use App\Models\ClassMaster;
use App\Models\CategoryMaster;
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
            ['email' => 'admin@erms.com'],
            [
                'name' => 'Board Super Admin',
                'password' => bcrypt('password'),
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
                'status' => 'Open',
            ],
        ];

        foreach ($exams as $exam) {
            Examination::updateOrCreate(['name' => $exam['name']], $exam);
        }

        // 6. Create Default Schools and School Admins
        $schools = [
            [
                'name' => 'YES RUIHSS PARED',
                'code' => 'YES001',
                'address' => 'POONCH',
                'zone' => 'POONCH',
                'state' => 'JAMMU AND KASHMIR',
                'contact_person' => 'RAHEEM VAZHAYIL',
                'mobile_number' => '9876543210',
                'email' => 'sch001@erms.com',
            ],
        ];

        foreach ($schools as $schoolData) {
            $school = School::updateOrCreate(['code' => $schoolData['code']], $schoolData);

            // Create School Admin
            $adminEmail = $schoolData['email'];
            $schoolAdmin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $schoolData['contact_person'] . ' (' . $schoolData['code'] . ')',
                    'password' => bcrypt('password'),
                    'school_id' => $school->id,
                ]
            );
            $schoolAdmin->assignRole($schoolAdminRole);
        }
    }
}
