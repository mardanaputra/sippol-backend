<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pengaduan;
use App\Models\Disposisi;
use Illuminate\Support\Facades\Http;

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
            'email' => 'admin@sippol.com',
            'password' => bcrypt('admin'),
            'role' => 'super_admin',
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

    public function test_login_rate_limiting_locks_after_3_failed_attempts(): void
    {
        // 1st attempt: fail
        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(401);

        // 2nd attempt: fail
        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(401);

        // 3rd attempt: fail
        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(401);

        // 4th attempt: throttled (429)
        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'message' => 'Terlalu banyak percobaan login. Akun Anda dibekukan sementara selama 5 menit.'
            ]);
    }

    public function test_login_fails_if_captcha_invalid(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false], 200),
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'admin',
            'captcha_token' => 'invalid-token',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => "Validasi Captcha gagal, silakan centang ulang kotak I'm not a robot."
            ]);
    }

    public function test_login_succeeds_if_captcha_valid(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'admin',
            'captcha_token' => 'valid-token',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil.',
            ]);
    }

    public function test_registration_fails_if_password_weak(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'johndoe@example.com',
            'password' => 'weak', // weak password (short, no mixedCase, no number, no symbol)
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_if_email_invalid(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'invalid-email',
            'password' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_if_email_taken(): void
    {
        // First user
        $this->postJson('/api/register', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'johndoe@example.com',
            'password' => 'SecurePass123!',
        ]);

        // Second user with same email
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'username' => 'janedoe',
            'email' => 'johndoe@example.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_succeeds_if_password_strong(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'username' => 'johndoe2',
            'email' => 'johndoe2@example.com',
            'password' => 'SecurePass123!', // strong password
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pengguna baru berhasil didaftarkan.',
            ]);
    }

    public function test_authenticated_admin_can_store_new_user(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/users', [
                'name' => 'Jane Admin',
                'username' => 'janeadmin',
                'email' => 'janeadmin@sippol.com',
                'password' => 'SecurePass123!',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pengguna baru berhasil didaftarkan.',
            ]);
    }

    public function test_unauthenticated_user_cannot_store_new_user(): void
    {
        $response = $this->postJson('/api/admin/users', [
            'name' => 'Jane Admin',
            'username' => 'janeadmin',
            'email' => 'janeadmin@sippol.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertStatus(401);
    }

    public function test_change_password_fails_if_password_weak(): void
    {
        $user = User::where('username', 'admin')->first();
        
        $response = $this->actingAs($user)
            ->postJson('/api/change-password', [
                'current_password' => 'admin',
                'new_password' => 'weak',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_change_password_succeeds_if_password_strong(): void
    {
        $user = User::where('username', 'admin')->first();
        
        $response = $this->actingAs($user)
            ->postJson('/api/change-password', [
                'current_password' => 'admin',
                'new_password' => 'SecurePass123!',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password Anda berhasil diperbarui.',
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

    public function test_only_super_admin_can_list_users(): void
    {
        // 1. Non-super admin cannot list users
        $regularAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($regularAdmin)
            ->getJson('/api/users');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
            ]);

        // 2. Super admin can list users
        $superAdmin = User::where('role', 'super_admin')->first();

        $responseSuper = $this->actingAs($superAdmin)
            ->getJson('/api/users');

        $responseSuper->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'users'
            ]);
    }

    public function test_non_super_admin_cannot_store_new_user(): void
    {
        $regularAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($regularAdmin)
            ->postJson('/api/admin/users', [
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'test@sippol.com',
                'password' => 'SecurePass123!',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
            ]);
    }

    public function test_only_super_admin_can_delete_user(): void
    {
        $targetUser = User::factory()->create(['role' => 'admin']);

        // 1. Non-super admin cannot delete user
        $regularAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($regularAdmin)
            ->deleteJson('/api/admin/users/' . $targetUser->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
            ]);

        // 2. Super admin can delete user
        $superAdmin = User::where('role', 'super_admin')->first();

        $responseSuper = $this->actingAs($superAdmin)
            ->deleteJson('/api/admin/users/' . $targetUser->id);

        $responseSuper->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id
        ]);
    }

    public function test_only_super_admin_can_access_activity_logs(): void
    {
        // 1. Regular admin gets 403
        $regularAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($regularAdmin)
            ->getJson('/api/admin/activity-logs');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
            ]);

        // 2. Super admin gets 200 and can see logs
        $superAdmin = User::where('role', 'super_admin')->first();

        $responseSuper = $this->actingAs($superAdmin)
            ->getJson('/api/admin/activity-logs');

        $responseSuper->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'logs'
            ]);
    }

    public function test_activity_logging_on_login_register_delete(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        // 1. Test LOGIN logging
        $responseLogin = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);
        $responseLogin->assertStatus(200);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'LOGIN',
        ]);

        // 2. Test TAMBAH_USER logging
        $responseStore = $this->actingAs($superAdmin)
            ->postJson('/api/admin/users', [
                'name' => 'New Admin Budi',
                'username' => 'budiadmin',
                'email' => 'budi@sippol.com',
                'password' => 'StrongPass123!',
            ]);
        $responseStore->assertStatus(201);

        $newAdmin = User::where('username', 'budiadmin')->first();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'TAMBAH USER',
            'description' => 'Mendaftarkan admin baru: budiadmin',
        ]);

        // 3. Test HAPUS_USER logging
        $responseDelete = $this->actingAs($superAdmin)
            ->deleteJson('/api/admin/users/' . $newAdmin->id);
        $responseDelete->assertStatus(200);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'HAPUS USER',
            'description' => 'Menghapus akun admin budiadmin',
        ]);
    }

    public function test_only_super_admin_can_change_user_role(): void
    {
        $targetUser = User::factory()->create(['role' => 'admin']);

        // 1. Regular admin gets 403
        $regularAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($regularAdmin)
            ->putJson('/api/admin/users/' . $targetUser->id . '/role', [
                'role' => 'super_admin'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
            ]);

        // 2. Super admin gets 422 if role is invalid
        $superAdmin = User::where('role', 'super_admin')->first();

        $responseInvalid = $this->actingAs($superAdmin)
            ->putJson('/api/admin/users/' . $targetUser->id . '/role', [
                'role' => 'invalid_role'
            ]);

        $responseInvalid->assertStatus(422)
            ->assertJsonValidationErrors(['role']);

        // 3. Super admin can successfully update role to super_admin
        $responseSuccess = $this->actingAs($superAdmin)
            ->putJson('/api/admin/users/' . $targetUser->id . '/role', [
                'role' => 'super_admin'
            ]);

        $responseSuccess->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Berhasil mengubah hak akses user menjadi super_admin'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'role' => 'super_admin'
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'UBAH ROLE',
            'description' => 'Mengubah hak akses user ' . $targetUser->username . ' menjadi super_admin',
        ]);
    }
}
