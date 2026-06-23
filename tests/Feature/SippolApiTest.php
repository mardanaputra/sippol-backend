<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pengaduan;
use App\Models\Disposisi;

class SippolApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the admin user
        User::factory()->create([
            'name' => 'Admin SIPPOL',
            'username' => 'admin',
            'password' => bcrypt('admin'),
        ]);
    }

    /**
     * Test admin login functionality.
     */
    public function test_admin_can_login_with_correct_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'user' => ['id', 'name', 'username']
            ])
            ->assertJson([
                'success' => true,
                'user' => [
                    'username' => 'admin'
                ]
            ]);
    }

    public function test_admin_cannot_login_with_incorrect_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Username atau Password yang Anda masukkan salah.'
            ]);
    }

    /**
     * Test public complaint submission and detail view.
     */
    public function test_public_can_submit_complaint(): void
    {
        $payload = [
            'id_tiket' => 'TKT-20260624-001',
            'nama_pelapor' => 'Wayan Sukra',
            'is_anonim' => false,
            'nomor_whatsapp' => '08123456789',
            'kategori_masalah' => 'Ketertiban Umum',
            'kronologi' => 'Ada pedagang kaki lima berjualan menutupi seluruh trotoar di jalan Diponegoro.',
            'latitude' => '-8.123456',
            'longitude' => '115.123456',
            'foto_bukti' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=', // tiny 1x1 png base64
        ];

        $response = $this->postJson('/api/pengaduan', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Laporan berhasil disimpan.'
            ]);

        $this->assertDatabaseHas('pengaduans', [
            'id_tiket' => 'TKT-20260624-001',
            'nama_pelapor' => 'Wayan Sukra',
            'status_laporan' => 'Pending'
        ]);
    }

    public function test_public_can_get_complaint_by_id(): void
    {
        $pengaduan = Pengaduan::create([
            'id_tiket' => 'TKT-20260624-002',
            'nama_pelapor' => 'Made Satria',
            'is_anonim' => true,
            'nomor_whatsapp' => '08123456780',
            'kategori_masalah' => 'Sampah Liar',
            'kronologi' => 'Tumpukan sampah berbau busuk di pojok pertigaan.',
            'latitude' => '-8.123456',
            'longitude' => '115.123456',
            'status_laporan' => 'Pending'
        ]);

        $response = $this->getJson('/api/pengaduan/' . $pengaduan->id_tiket);

        $response->assertStatus(200)
            ->assertJson([
                'id_tiket' => 'TKT-20260624-002',
                'nama_pelapor' => 'Made Satria',
                'is_anonim' => true,
            ]);
    }

    /**
     * Test admin routes authorization.
     */
    public function test_unauthenticated_user_cannot_access_admin_endpoints(): void
    {
        // Get complaints
        $this->getJson('/api/pengaduan')
            ->assertStatus(401);

        // Post disposition
        $this->postJson('/api/disposisi', [])
            ->assertStatus(401);

        // Delete complaint
        $this->deleteJson('/api/pengaduan/TKT-001')
            ->assertStatus(401);
    }

    public function test_authenticated_admin_can_access_complaint_list(): void
    {
        $admin = User::first();
        $token = $admin->createToken('test-token')->plainTextToken;

        Pengaduan::create([
            'id_tiket' => 'TKT-20260624-003',
            'nama_pelapor' => 'Ketut Budiasa',
            'is_anonim' => false,
            'nomor_whatsapp' => '08123456788',
            'kategori_masalah' => 'PKL',
            'kronologi' => 'Penertiban PKL depan pasar.',
            'latitude' => '-8.123456',
            'longitude' => '115.123456',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/pengaduan');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    /**
     * Test disposition creation.
     */
    public function test_admin_can_create_disposition(): void
    {
        $admin = User::first();
        $token = $admin->createToken('test-token')->plainTextToken;

        $pengaduan = Pengaduan::create([
            'id_tiket' => 'TKT-20260624-004',
            'nama_pelapor' => 'Luh Putu',
            'is_anonim' => false,
            'nomor_whatsapp' => '08123456787',
            'kategori_masalah' => 'Kemacetan',
            'kronologi' => 'Parkir liar di jalan Udayana.',
            'latitude' => '-8.123456',
            'longitude' => '115.123456',
            'status_laporan' => 'Pending'
        ]);

        $payload = [
            'id_tiket' => $pengaduan->id_tiket,
            'nama_admin' => 'Administrator Utama',
            'waktu_verifikasi' => now()->toISOString(),
            'bidang_tujuan' => 'Bidang Trantibum',
            'kedaruratan' => 'Tinggi',
            'catatan' => 'Segera terjunkan regu patroli ke lokasi.',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/disposisi', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Disposisi laporan berhasil dikirim dan dicatat.'
            ]);

        // Assert disposition exists in DB
        $this->assertDatabaseHas('disposisis', [
            'id_tiket' => $pengaduan->id_tiket,
            'nama_admin' => 'Administrator Utama',
            'bidang_tujuan' => 'Bidang Trantibum',
            'kedaruratan' => 'Tinggi'
        ]);

        // Assert pengaduan status has changed to 'Disposisi' and bidang_disposisi matches
        $this->assertDatabaseHas('pengaduans', [
            'id_tiket' => $pengaduan->id_tiket,
            'status_laporan' => 'Disposisi',
            'bidang_disposisi' => 'Bidang Trantibum'
        ]);
    }

    public function test_admin_cannot_disposition_same_complaint_twice(): void
    {
        $admin = User::first();
        $token = $admin->createToken('test-token')->plainTextToken;

        $pengaduan = Pengaduan::create([
            'id_tiket' => 'TKT-20260624-005',
            'nama_pelapor' => 'Nyoman Sukma',
            'is_anonim' => false,
            'nomor_whatsapp' => '08123456786',
            'kategori_masalah' => 'Ketertiban',
            'kronologi' => 'Penjual petasan liar.',
            'latitude' => '-8.123456',
            'longitude' => '115.123456',
            'status_laporan' => 'Disposisi',
            'bidang_disposisi' => 'Bidang Linmas'
        ]);

        Disposisi::create([
            'id_tiket' => $pengaduan->id_tiket,
            'nama_admin' => 'Admin 1',
            'waktu_verifikasi' => now(),
            'bidang_tujuan' => 'Bidang Linmas',
            'kedaruratan' => 'Sedang',
            'catatan' => 'Sudah diproses.',
            'waktu_dikirim' => now()
        ]);

        $payload = [
            'id_tiket' => $pengaduan->id_tiket,
            'nama_admin' => 'Admin 2',
            'bidang_tujuan' => 'Bidang Trantibum',
            'kedaruratan' => 'Tinggi',
            'catatan' => 'Coba disposisi ulang.',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/disposisi', $payload);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Laporan dengan tiket ini sudah pernah didisposisikan sebelumnya.'
            ]);
    }

    /**
     * Test admin can delete complaint.
     */
    public function test_admin_can_delete_complaint(): void
    {
        $admin = User::first();
        $token = $admin->createToken('test-token')->plainTextToken;

        $pengaduan = Pengaduan::create([
            'id_tiket' => 'TKT-20260624-006',
            'nama_pelapor' => 'Spam Bot',
            'is_anonim' => false,
            'nomor_whatsapp' => '08123456785',
            'kategori_masalah' => 'Spam',
            'kronologi' => 'Spam message text.',
            'latitude' => '-8.123456',
            'longitude' => '115.123456',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/pengaduan/' . $pengaduan->id_tiket);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Laporan TKT-20260624-006 berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('pengaduans', [
            'id_tiket' => 'TKT-20260624-006'
        ]);
    }
}
