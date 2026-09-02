<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $superAdminRole;
    protected $invigilatorRole;
    protected $centre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $this->invigilatorRole = Role::firstOrCreate(['name' => 'invigilator', 'guard_name' => 'web']);

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@erms.com',
            'password' => bcrypt('password'),
        ]);
        $this->superAdmin->assignRole($this->superAdminRole);

        $this->centre = School::create([
            'name' => 'St. Mary Examination Centre',
            'code' => 'CENTRE-101',
            'email' => 'stmary@centre.com',
            'phone' => '9876543210',
            'address' => 'Test Centre Address',
            'is_centre' => true,
            'status' => true,
        ]);

        \App\Models\Examination::create([
            'name' => 'Annual Board Examination 2026',
            'academic_year' => '2025-2026',
            'registration_start_date' => now()->subDays(10),
            'registration_end_date' => now()->addDays(20),
            'hall_ticket_release_date' => now()->addDays(25),
            'status' => 'Registration Started',
        ]);
    }

    /**
     * Test super admin can access staff index page.
     */
    public function test_super_admin_can_view_staff_index_page(): void
    {
        $invigilator1 = User::create([
            'name' => 'John Invigilator',
            'email' => 'john@invigilator.com',
            'password' => bcrypt('password'),
            'school_id' => $this->centre->id,
            'last_login_at' => now(),
        ]);
        $invigilator1->assignRole($this->invigilatorRole);

        $invigilator2 = User::create([
            'name' => 'Jane Invigilator',
            'email' => 'jane@invigilator.com',
            'password' => bcrypt('password'),
            'school_id' => null,
            'last_login_at' => null,
        ]);
        $invigilator2->assignRole($this->invigilatorRole);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.staff.index'));

        $response->assertStatus(200);
        $response->assertSee('John Invigilator');
        $response->assertSee('Jane Invigilator');
        $response->assertSee('St. Mary Examination Centre');
        $response->assertSee('Board Invigilator (All Centers)');
        $response->assertSee('Logged In');
        $response->assertSee('Not Logged In');
        $response->assertSee(route('admin.staff.export-pdf'));
    }

    /**
     * Test filtering staff by login_status.
     */
    public function test_filter_staff_by_login_status(): void
    {
        $loggedInStaff = User::create([
            'name' => 'Active Logged In Staff',
            'email' => 'loggedin@staff.com',
            'password' => bcrypt('password'),
            'school_id' => $this->centre->id,
            'last_login_at' => now(),
        ]);
        $loggedInStaff->assignRole($this->invigilatorRole);

        $notLoggedInStaff = User::create([
            'name' => 'Not Logged Staff',
            'email' => 'notlogged@staff.com',
            'password' => bcrypt('password'),
            'school_id' => $this->centre->id,
            'last_login_at' => null,
        ]);
        $notLoggedInStaff->assignRole($this->invigilatorRole);

        // Filter logged in
        $response1 = $this->actingAs($this->superAdmin)->get(route('admin.staff.index', ['login_status' => 'logged_in']));
        $response1->assertStatus(200);
        $response1->assertSee('Active Logged In Staff');
        $response1->assertDontSee('Not Logged Staff');

        // Filter not logged in
        $response2 = $this->actingAs($this->superAdmin)->get(route('admin.staff.index', ['login_status' => 'not_logged_in']));
        $response2->assertStatus(200);
        $response2->assertSee('Not Logged Staff');
        $response2->assertDontSee('Active Logged In Staff');
    }

    /**
     * Test exporting invigilators list as PDF.
     */
    public function test_super_admin_can_export_invigilators_pdf(): void
    {
        $invigilator1 = User::create([
            'name' => 'Alex Invigilator',
            'email' => 'alex@invigilator.com',
            'password' => bcrypt('password'),
            'school_id' => $this->centre->id,
            'last_login_at' => now(),
        ]);
        $invigilator1->assignRole($this->invigilatorRole);

        $invigilator2 = User::create([
            'name' => 'Bob Invigilator',
            'email' => 'bob@invigilator.com',
            'password' => bcrypt('password'),
            'school_id' => null,
            'last_login_at' => null,
        ]);
        $invigilator2->assignRole($this->invigilatorRole);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.staff.export-pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test unauthorized users cannot export invigilators PDF.
     */
    public function test_unauthorized_users_cannot_export_staff_pdf(): void
    {
        // Unauthenticated
        $response = $this->get(route('admin.staff.export-pdf'));
        $response->assertRedirect(route('login'));

        // School Admin
        $schoolAdminRole = Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        $schoolAdmin = User::create([
            'name' => 'School Principal',
            'email' => 'principal@school.com',
            'password' => bcrypt('password'),
        ]);
        $schoolAdmin->assignRole($schoolAdminRole);

        $response = $this->actingAs($schoolAdmin)->get(route('admin.staff.export-pdf'));
        $response->assertStatus(403);
    }
}
