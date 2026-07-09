<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pengaduan;

class PengaduanValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complaint_form_can_be_submitted_with_valid_data()
    {
        $payload = [
            'id_tiket' => 'TKT-' . time(),
            'nama_pelapor' => 'Budi Santoso',
            'is_anonim' => false,
            'nomor_whatsapp' => '081234567890',
            'kategori_masalah' => 'Ketertiban Umum',
            'kronologi' => 'Ada gangguan ketertiban di pasar',
            'latitude' => '-8.112000',
            'longitude' => '115.088000',
            'foto_bukti' => 'base64image_string'
        ];

        $response = $this->postJson('/api/pengaduan', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Laporan berhasil disimpan.',
                 ]);
                 
        $this->assertDatabaseHas('pengaduans', [
            'id_tiket' => $payload['id_tiket'],
            'nama_pelapor' => 'Budi Santoso',
        ]);
    }

    /** @test */
    public function complaint_form_validation_fails_when_required_fields_are_missing()
    {
        $response = $this->postJson('/api/pengaduan', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'id_tiket',
                     'nama_pelapor',
                     'nomor_whatsapp',
                     'kategori_masalah',
                     'kronologi',
                     'latitude',
                     'longitude',
                 ]);
    }

    /** @test */
    public function complaint_form_validation_fails_when_id_tiket_is_duplicate()
    {
        // Insert a complaint first
        $existingTicketId = 'TKT-DUPLICATE';
        Pengaduan::create([
            'id_tiket' => $existingTicketId,
            'nama_pelapor' => 'Ayu',
            'is_anonim' => true,
            'nomor_whatsapp' => '08111222333',
            'kategori_masalah' => 'Pelanggaran Perda',
            'kronologi' => 'Test kronologi',
            'latitude' => '-8.123',
            'longitude' => '115.123',
            'status_laporan' => 'Pending',
        ]);

        $payload = [
            'id_tiket' => $existingTicketId,
            'nama_pelapor' => 'Budi',
            'nomor_whatsapp' => '08999888777',
            'kategori_masalah' => 'Pelanggaran K3',
            'kronologi' => 'Test',
            'latitude' => '-8.111',
            'longitude' => '115.222',
        ];

        $response = $this->postJson('/api/pengaduan', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_tiket']);
    }
}
