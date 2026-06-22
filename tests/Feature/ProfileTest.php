<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $schoolAdmin;
    protected $school;
    protected $invigilator;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $schoolAdminRole = Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        $invigilatorRole = Role::firstOrCreate(['name' => 'invigilator', 'guard_name' => 'web']);

        // Create Super Admin user
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@erms.com',
            'password' => bcrypt('password'),
        ]);
        $this->superAdmin->assignRole($superAdminRole);

        // Create School & School Admin user
        $this->school = School::create([
            'name' => 'Test High School',
            'code' => 'SCH999',
            'address' => '123 Test Street',
            'zone' => 'South Zone',
            'state' => 'Kerala',
            'contact_person' => 'School Principal',
            'mobile_number' => '9999999999',
            'email' => 'principal@test.com',
            'status' => true,
        ]);

        $this->schoolAdmin = User::create([
            'name' => 'School Admin',
            'email' => 'principal@test.com',
            'password' => bcrypt('password'),
            'school_id' => $this->school->id,
        ]);
        $this->schoolAdmin->assignRole($schoolAdminRole);

        // Create Invigilator user
        $this->invigilator = User::create([
            'name' => 'Test Invigilator',
            'email' => 'invigilator@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->invigilator->assignRole($invigilatorRole);
    }

    /**
     * Guest cannot access profile pages.
     */
    public function test_guest_cannot_access_profile_pages(): void
    {
        $response1 = $this->get(route('admin.profile.edit'));
        $response1->assertRedirect(route('login'));

        $response2 = $this->get(route('school.profile.edit'));
        $response2->assertRedirect(route('login'));
    }

    /**
     * School Admin cannot access Super Admin profile.
     */
    public function test_school_admin_cannot_access_super_admin_profile(): void
    {
        $response = $this->actingAs($this->schoolAdmin)
            ->get(route('admin.profile.edit'));
        
        $response->assertStatus(403);
    }

    /**
     * Super Admin can view and update their profile.
     */
    public function test_super_admin_can_view_and_update_profile(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Super Admin');
        $response->assertSee('admin@erms.com');

        $updateResponse = $this->actingAs($this->superAdmin)
            ->put(route('admin.profile.update'), [
                'name' => 'Updated Super Admin',
                'email' => 'newadmin@erms.com',
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->superAdmin->id,
            'name' => 'Updated Super Admin',
            'email' => 'newadmin@erms.com',
        ]);
    }

    /**
     * School Admin can view and update their profile and school details.
     */
    public function test_school_admin_can_view_and_update_profile(): void
    {
        $response = $this->actingAs($this->schoolAdmin)
            ->get(route('school.profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('School Admin');
        $response->assertSee('principal@test.com');
        $response->assertSee('Test High School');
        $response->assertSee('SCH999');

        $updateResponse = $this->actingAs($this->schoolAdmin)
            ->put(route('school.profile.update'), [
                'name' => 'Updated School Admin',
                'email' => 'newprincipal@test.com',
                'school_name' => 'Updated Test School',
                'address' => '456 New Road',
                'zone' => 'Central Zone',
                'state' => 'Karnataka',
                'contact_person' => 'New Principal',
                'mobile_number' => '8888888888',
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->schoolAdmin->id,
            'name' => 'Updated School Admin',
            'email' => 'newprincipal@test.com',
        ]);

        $this->assertDatabaseHas('schools', [
            'id' => $this->school->id,
            'name' => 'Updated Test School',
            'code' => 'SCH999', // should remain same
            'address' => '456 New Road',
            'zone' => 'Central Zone',
            'state' => 'Karnataka',
            'contact_person' => 'New Principal',
            'mobile_number' => '8888888888',
            'email' => 'newprincipal@test.com', // synced email
        ]);
    }

    /**
     * Invigilator can view their profile.
     */
    public function test_invigilator_can_view_profile(): void
    {
        $response = $this->actingAs($this->invigilator)
            ->get(route('invigilator.profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Test Invigilator');
        $response->assertSee('invigilator@test.com');
    }

    /**
     * Invigilator can change their password.
     */
    public function test_invigilator_can_change_password(): void
    {
        $response = $this->actingAs($this->invigilator)
            ->post(route('password.update'), [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // Try to login with new password
        $this->assertTrue(auth()->attempt([
            'email' => 'invigilator@test.com',
            'password' => 'newpassword123',
        ]));
    }
}
