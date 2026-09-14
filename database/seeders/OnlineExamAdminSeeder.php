<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class OnlineExamAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or retrieve Permissions
        $permissions = [
            'view online exams',
            'create online exams',
            'edit online exams',
            'publish online exams',
            'manage online questions',
            'manage online students',
            'monitor online exam live',
            'terminate student exam session',
            'view online exam results',
            'export online exam reports',
            'manage online exam settings',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Create 'exam-admin' role and assign permissions
        $examAdminRole = Role::firstOrCreate(['name' => 'exam-admin', 'guard_name' => 'web']);
        $examAdminRole->syncPermissions($permissions);

        // Also give Super Admin all these permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo($permissions);

        // 3. Create up to 5 Dedicated Online Examination Administrators
        $admins = [
            [
                'name' => 'Exam Admin 1',
                'email' => 'examadmin1@ermis.test',
                'password' => bcrypt('ExamAdmin@123#'),
            ],
            [
                'name' => 'Exam Admin 2',
                'email' => 'examadmin2@ermis.test',
                'password' => bcrypt('ExamAdmin@234#'),
            ],
            [
                'name' => 'Exam Admin 3',
                'email' => 'examadmin3@ermis.test',
                'password' => bcrypt('ExamAdmin@345#'),
            ],
            [
                'name' => 'Exam Admin 4',
                'email' => 'examadmin4@ermis.test',
                'password' => bcrypt('ExamAdmin@456#'),
            ],
            [
                'name' => 'Exam Admin 5',
                'email' => 'examadmin5@ermis.test',
                'password' => bcrypt('ExamAdmin@567#'),
            ],
        ];

        foreach ($admins as $adminData) {
            $adminUser = User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => $adminData['password'],
                    'is_active' => true,
                    'school_id' => null,
                ]
            );

            if (!$adminUser->hasRole('exam-admin')) {
                $adminUser->assignRole($examAdminRole);
            }
        }
    }
}
