<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function staff_bidang_trantib_cannot_access_linmas_endpoints()
    {
        // Simulate a Staff Bidang Trantib
        $userTrantib = User::create([
            'name' => 'Staff Trantib',
            'username' => 'staff_trantib',
            'email' => 'trantib@example.com',
            'password' => bcrypt('password123'),
            'role' => 'bidang_trantib',
        ]);

        Sanctum::actingAs($userTrantib, ['*']);

        // Attempt to access Linmas endpoint
        $response = $this->getJson('/api/linmas/satlinmas');

        // This SHOULD return 403 Forbidden based on proper role security
        // If it returns 200, it means there is a security flaw
        $response->assertStatus(403);
    }

    /** @test */
    public function staff_bidang_linmas_cannot_access_super_admin_endpoints()
    {
        $userLinmas = User::create([
            'name' => 'Staff Linmas',
            'username' => 'staff_linmas',
            'email' => 'linmas@example.com',
            'password' => bcrypt('password123'),
            'role' => 'bidang_linmas',
        ]);

        Sanctum::actingAs($userLinmas, ['*']);

        // Attempt to access Super Admin endpoint (get all users)
        $response = $this->getJson('/api/users');

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
                 ]);
    }

    /** @test */
    public function super_admin_can_access_all_endpoints()
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        Sanctum::actingAs($superAdmin, ['*']);

        // Access users endpoint
        $this->getJson('/api/users')->assertStatus(200);

        // Access Trantib endpoint (should return 200)
        $this->getJson('/api/trantib/patroli')->assertStatus(200);
        
        // Access Linmas endpoint (should return 200)
        $this->getJson('/api/linmas/satlinmas')->assertStatus(200);
    }
}
