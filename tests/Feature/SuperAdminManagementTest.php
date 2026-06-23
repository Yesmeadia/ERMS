<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuperAdminLoginAlertMail;
use App\Mail\SuperAdminCreatedMail;

class SuperAdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $superAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed role
        $this->superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        // Create initial Super Admin user (like seeded one)
        $this->superAdmin = User::create([
            'name' => 'Seeded Super Admin',
            'email' => 'admin@erms.com',
            'password' => bcrypt('password'),
        ]);
        $this->superAdmin->assignRole($this->superAdminRole);
    }

    /**
     * Test login triggers email notification alert for super-admin.
     */
    public function test_login_sends_login_alert_email(): void
    {
        Mail::fake();

        $response = $this->post(route('login'), [
            'email' => 'admin@erms.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->superAdmin);

        Mail::assertSent(SuperAdminLoginAlertMail::class, function ($mail) {
            return $mail->hasTo('admin@erms.com') && $mail->user->id === $this->superAdmin->id;
        });
    }

    /**
     * Test only super admin can access super admin management.
     */
    public function test_unauthorized_user_cannot_access_super_admin_management(): void
    {
        $schoolAdminRole = Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        $schoolAdmin = User::create([
            'name' => 'School Principal',
            'email' => 'school@test.com',
            'password' => bcrypt('password'),
        ]);
        $schoolAdmin->assignRole($schoolAdminRole);

        $response = $this->actingAs($schoolAdmin)->get(route('admin.admins.index'));
        $response->assertStatus(403);
    }

    /**
     * Test Super Admin can list existing super admins.
     */
    public function test_super_admin_can_list_super_admins(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.admins.index'));
        $response->assertStatus(200);
        $response->assertSee('admin@erms.com');
    }

    /**
     * Test Super Admin can create exactly 1 other Super Admin.
     */
    public function test_super_admin_can_create_one_additional_super_admin(): void
    {
        Mail::fake();

        // 1. Initial count is 1
        $this->assertEquals(1, User::role('super-admin')->count());

        // 2. Can access create page
        $response = $this->actingAs($this->superAdmin)->get(route('admin.admins.create'));
        $response->assertStatus(200);

        // 3. Store second super admin
        $responseStore = $this->actingAs($this->superAdmin)->post(route('admin.admins.store'), [
            'name' => 'Second Super Admin',
            'email' => 'second@erms.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $responseStore->assertRedirect(route('admin.admins.index'));
        $responseStore->assertSessionHas('success');

        $this->assertEquals(2, User::role('super-admin')->count());
        $this->assertDatabaseHas('users', [
            'email' => 'second@erms.com',
        ]);

        // Assert welcome email is sent to the new admin
        Mail::assertSent(SuperAdminCreatedMail::class, function ($mail) {
            return $mail->hasTo('second@erms.com');
        });

        // 4. Try to navigate to create page when limit reached (2 admins exist)
        $responseBlocked = $this->actingAs($this->superAdmin)->get(route('admin.admins.create'));
        $responseBlocked->assertRedirect(route('admin.admins.index'));
        $responseBlocked->assertSessionHas('error', 'The maximum limit of 2 Super Admin accounts has been reached.');

        // 5. Try to store third super admin
        $responseStoreBlocked = $this->actingAs($this->superAdmin)->post(route('admin.admins.store'), [
            'name' => 'Third Super Admin',
            'email' => 'third@erms.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $responseStoreBlocked->assertRedirect(route('admin.admins.index'));
        $responseStoreBlocked->assertSessionHas('error', 'The maximum limit of 2 Super Admin accounts has been reached.');
        $this->assertEquals(2, User::role('super-admin')->count());
    }

    /**
     * Test Super Admin cannot edit/delete themselves via this controller.
     */
    public function test_super_admin_cannot_edit_or_delete_themselves(): void
    {
        // Try edit self
        $responseEdit = $this->actingAs($this->superAdmin)->get(route('admin.admins.edit', $this->superAdmin->id));
        $responseEdit->assertRedirect(route('admin.admins.index'));
        $responseEdit->assertSessionHas('error', 'Please edit your own account details via the My Profile page.');

        // Try update self
        $responseUpdate = $this->actingAs($this->superAdmin)->put(route('admin.admins.update', $this->superAdmin->id), [
            'name' => 'New Name',
            'email' => 'admin@erms.com',
        ]);
        $responseUpdate->assertRedirect(route('admin.admins.index'));
        $responseUpdate->assertSessionHas('error', 'Please edit your own account details via the My Profile page.');

        // Try delete self
        $responseDelete = $this->actingAs($this->superAdmin)->delete(route('admin.admins.destroy', $this->superAdmin->id));
        $responseDelete->assertRedirect(route('admin.admins.index'));
        $responseDelete->assertSessionHas('error', 'You cannot delete your own active Super Admin account.');

        // Verify count still 1
        $this->assertEquals(1, User::role('super-admin')->count());
    }
}
