<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ReguPatroli;
use App\Models\PenertibanK3;
use App\Models\Satlinmas;
use App\Models\PenertibanTrantibum;
use App\Models\KegiatanLinmas;
use App\Models\PerdaPerbup;
use App\Models\KatalogPelanggaran;
use App\Models\PenegakanPerada;
use App\Models\SdaPersonel;
use App\Models\SdaKegiatan;
use App\Models\SdaPustaka;
use App\Models\SatpolKegiatan;

class SippolPhase2Test extends TestCase
{
    use RefreshDatabase;

    protected string $token;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the admin user
        $this->admin = User::factory()->create([
            'name' => 'Admin SIPPOL',
            'username' => 'admin',
            'password' => bcrypt('admin'),
            'role' => 'super_admin',
        ]);

        $this->token = $this->admin->createToken('test-token')->plainTextToken;
    }

    /**
     * Helper to get auth header.
     */
    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Test Modul Trantibum.
     */
    public function test_trantib_patroli_crud(): void
    {
        // 1. Create (Store)
        $payload = [
            'shift_kerja' => 'Pagi',
            'komandan_regu' => 'Danru A',
            'anggota_regu' => ['Agus', 'Wayan'],
            'wilayah_patroli' => ['Kecamatan Buleleng', 'Sukasada'],
            'kendaraan_dinas' => 'Motor Trail KLX',
        ];

        $response = $this->postJson('/api/trantib/patroli', $payload, $this->authHeaders());
        $response->assertStatus(201);
        $this->assertDatabaseHas('regu_patrolis', [
            'shift_kerja' => 'Pagi',
            'komandan_regu' => 'Danru A',
            'anggota_regu' => 'Agus, Wayan',
        ]);

        $patrol = ReguPatroli::first();

        // 2. Read (Index)
        $this->getJson('/api/trantib/patroli', $this->authHeaders())
            ->assertStatus(200)
            ->assertJsonCount(1);

        // 3. Update
        $updatePayload = [
            'id' => $patrol->id,
            'shift_kerja' => 'Malam',
            'komandan_regu' => 'Danru A Updated',
            'anggota_regu' => 'Agus, Wayan, Made',
            'wilayah_patroli' => 'Kecamatan Buleleng',
            'kendaraan_dinas' => 'Truk Operasional',
        ];

        $this->putJson('/api/trantib/patroli', $updatePayload, $this->authHeaders())
            ->assertStatus(200);

        $this->assertDatabaseHas('regu_patrolis', [
            'id' => $patrol->id,
            'shift_kerja' => 'Malam',
            'komandan_regu' => 'Danru A Updated',
        ]);

        // 4. Delete
        $this->deleteJson('/api/trantib/patroli?id=' . $patrol->id, [], $this->authHeaders())
            ->assertStatus(200);

        $this->assertDatabaseMissing('regu_patrolis', ['id' => $patrol->id]);
    }

    /**
     * Test Modul Linmas.
     */
    public function test_linmas_satlinmas_crud(): void
    {
        $payload = [
            'kecamatan' => 'Buleleng',
            'desa' => 'Banjar',
            'petugas_pendata' => 'Staf Linmas',
        ];

        $response = $this->postJson('/api/linmas/satlinmas', $payload, $this->authHeaders());
        $response->assertStatus(201);
        $this->assertDatabaseHas('satlinmas', [
            'kecamatan' => 'Buleleng',
            'desa' => 'Banjar',
        ]);

        $satlinmas = Satlinmas::first();

        $this->getJson('/api/linmas/satlinmas', $this->authHeaders())
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->deleteJson('/api/linmas/satlinmas?id=' . $satlinmas->id, [], $this->authHeaders())
            ->assertStatus(200);

        $this->assertDatabaseMissing('satlinmas', ['id' => $satlinmas->id]);
    }

    /**
     * Test Modul Perada.
     */
    public function test_perada_regulasi_and_pelanggaran_crud(): void
    {
        // 1. Create Regulation
        $payload = [
            'jenis_peraturan' => 'Perda',
            'nomor_peraturan' => 'No 5',
            'tahun_peraturan' => 2026,
            'judul_tentang' => 'Ketertiban Umum',
        ];

        $response = $this->postJson('/api/perada/regulasi', $payload, $this->authHeaders());
        $response->assertStatus(201);
        
        $reg = PerdaPerbup::first();
        $this->assertNotNull($reg->kode_regulasi);

        // 2. Create Violation Catalog linking to regulation
        $catalogPayload = [
            'kode_regulasi' => $reg->kode_regulasi,
            'pasal' => 'Pasal 3',
            'jenis_pelanggaran' => 'Membuang sampah di jalan',
            'denda_maksimal' => 500000.0,
        ];

        $responseCat = $this->postJson('/api/perada/pelanggaran', $catalogPayload, $this->authHeaders());
        $responseCat->assertStatus(201);

        $this->assertDatabaseHas('katalog_pelanggarans', [
            'kode_regulasi' => $reg->kode_regulasi,
            'pasal' => 'Pasal 3',
            'denda_maksimal' => 500000.0,
        ]);
    }

    /**
     * Test Modul SDA.
     */
    public function test_sda_personel_crud(): void
    {
        $payload = [
            'nip_kontrak' => '199001012026011001',
            'nama_lengkap' => 'Wayan Dapet',
            'status_kepegawaian' => 'ASN',
            'pangkat_golongan' => 'Penata Muda / IIIa',
            'jabatan' => 'Fungsional Umum',
            'penempatan_bidang' => 'SDA',
            'rekam_pelatihan' => ['Bintek Linmas', 'Diklat PPNS'],
            'status_keaktifan' => 'Aktif',
        ];

        $response = $this->postJson('/api/sda/personel', $payload, $this->authHeaders());
        $response->assertStatus(201);

        $this->assertDatabaseHas('sda_personels', [
            'nip_kontrak' => '199001012026011001',
            'nama_lengkap' => 'Wayan Dapet',
            'rekam_pelatihan' => 'Bintek Linmas, Diklat PPNS',
        ]);
    }

    /**
     * Test Modul Portal Kegiatan.
     */
    public function test_satpol_kegiatan_crud(): void
    {
        $payload = [
            'bidang' => 'Bidang Trantibum',
            'jenis_kegiatan' => 'Patroli Rutin',
            'lokasi' => 'Taman Kota Singaraja',
            'uraian_kegiatan' => 'Melaksanakan patroli ketertiban di seputaran taman kota.',
        ];

        $response = $this->postJson('/api/admin/kegiatan', $payload, $this->authHeaders());
        $response->assertStatus(201);

        $this->assertDatabaseHas('satpol_kegiatans', [
            'bidang' => 'Bidang Trantibum',
            'jenis_kegiatan' => 'Patroli Rutin',
            'lokasi' => 'Taman Kota Singaraja',
        ]);
    }
}
