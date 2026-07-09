<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pengaduan;
use App\Models\Disposisi;
use Laravel\Sanctum\Sanctum;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);
    }

    /** @test */
    public function admin_can_disposisi_pengaduan_and_status_changes_to_disposisi()
    {
        Sanctum::actingAs($this->superAdmin, ['*']);

        // Create a pending complaint
        $pengaduan = Pengaduan::create([
            'id_tiket' => 'TKT-CRUD-001',
            'nama_pelapor' => 'Warga Test',
            'nomor_whatsapp' => '0811223344',
            'kategori_masalah' => 'PKL',
            'kronologi' => 'Ada PKL',
            'latitude' => '-8.123',
            'longitude' => '115.123',
            'status_laporan' => 'Pending',
        ]);

        $payload = [
            'id_tiket' => 'TKT-CRUD-001',
            'nama_admin' => $this->superAdmin->name,
            'bidang_tujuan' => 'Bidang Trantib',
            'kedaruratan' => 'Segera',
            'catatan' => 'Tolong segera dicek.',
        ];

        $response = $this->postJson('/api/disposisi', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true);

        // Verify Database Changes
        $this->assertDatabaseHas('disposisis', [
            'id_tiket' => 'TKT-CRUD-001',
            'bidang_tujuan' => 'Bidang Trantib',
        ]);

        // Verify Pengaduan status and bidang changed
        $this->assertDatabaseHas('pengaduans', [
            'id_tiket' => 'TKT-CRUD-001',
            'status_laporan' => 'Disposisi',
            'bidang_disposisi' => 'Bidang Trantib',
        ]);
    }

    /** @test */
    public function admin_cannot_disposisi_already_disposisied_pengaduan()
    {
        Sanctum::actingAs($this->superAdmin, ['*']);

        // Create a complaint that already has a disposition
        $pengaduan = Pengaduan::create([
            'id_tiket' => 'TKT-CRUD-002',
            'nama_pelapor' => 'Warga Test 2',
            'nomor_whatsapp' => '0811223355',
            'kategori_masalah' => 'ODGJ',
            'kronologi' => 'Ada ODGJ',
            'latitude' => '-8.123',
            'longitude' => '115.123',
            'status_laporan' => 'Disposisi',
            'bidang_disposisi' => 'Bidang Linmas',
        ]);

        Disposisi::create([
            'id_tiket' => 'TKT-CRUD-002',
            'nama_admin' => 'Admin Utama',
            'bidang_tujuan' => 'Bidang Linmas',
            'kedaruratan' => 'Segera',
            'catatan' => 'Sedang ditangani',
        ]);

        $payload = [
            'id_tiket' => 'TKT-CRUD-002',
            'nama_admin' => $this->superAdmin->name,
            'bidang_tujuan' => 'Bidang Trantib',
            'kedaruratan' => 'Biasa',
            'catatan' => 'Re-disposisi',
        ];

        // Attempt to create disposition again
        $response = $this->postJson('/api/disposisi', $payload);

        $response->assertStatus(400)
                 ->assertJsonPath('error', 'Laporan dengan tiket ini sudah pernah didisposisikan sebelumnya.');
    }
}
