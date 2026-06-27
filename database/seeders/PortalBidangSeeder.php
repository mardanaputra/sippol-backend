<?php

namespace Database\Seeders;

use App\Models\ReguPatroli;
use App\Models\PerdaPerbup;
use App\Models\SdaPersonel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PortalBidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Portal Trantib (Regu Patroli)
        $regus = [
            [
                'no_spt' => 'SPT/TRANTIB/2026/001',
                'tanggal_penugasan' => Carbon::now()->setTime(8, 0),
                'shift_kerja' => 'Pagi',
                'komandan_regu' => 'I Made Widastra, S.Sos.',
                'anggota_regu' => 'Wayan Sukra, Nyoman Triadi, Ketut Merta, Gede Sumarta',
                'wilayah_patroli' => 'Pusat Kota Singaraja (Jl. Ngurah Rai, Jl. Udayana, Jl. Gajah Mada)',
                'keterangan_area' => 'Pengawasan kawasan tertib lalu lintas, penertiban PKL liar di trotoar. Status: Aktif',
                'kendaraan_dinas' => 'Mobil Patroli Panther 01',
                'surat_tugas' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'no_spt' => 'SPT/TRANTIB/2026/002',
                'tanggal_penugasan' => Carbon::now()->setTime(14, 0),
                'shift_kerja' => 'Siang',
                'komandan_regu' => 'Ketut Suardana',
                'anggota_regu' => 'Putu Yudha, Made Sudarsana, I Kadek Suardika',
                'wilayah_patroli' => 'Kawasan Patroli Wilayah Barat (Seririt & Banjar)',
                'keterangan_area' => 'Pemantauan ketertiban pasar tumpah Seririt dan kawasan wisata pesisir Lovina. Status: Aktif',
                'kendaraan_dinas' => 'Mobil Patroli Hilux 02',
                'surat_tugas' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'no_spt' => 'SPT/TRANTIB/2026/003',
                'tanggal_penugasan' => Carbon::now()->setTime(20, 0),
                'shift_kerja' => 'Malam',
                'komandan_regu' => 'Gede Astawa',
                'anggota_regu' => 'Komang Agus, Ketut Widiana, Dewa Gede Raka',
                'wilayah_patroli' => 'Kawasan Pengamanan Pantai Penimbangan & Eks Pelabuhan Buleleng',
                'keterangan_area' => 'Patroli malam antisipasi balap liar dan kerumunan malam yang mengganggu KTR. Status: Aktif',
                'kendaraan_dinas' => 'Motor Trail Kawasaki KLX Regu A',
                'surat_tugas' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($regus as $regu) {
            ReguPatroli::create($regu);
        }

        // 2. Seed Portal Perada (Tabel Regulasi / Perda Perbup)
        $regulasis = [
            [
                'kode_regulasi' => 'REG-2009-006',
                'jenis_peraturan' => 'PERDA',
                'nomor_peraturan' => '6',
                'tahun_peraturan' => 2009,
                'judul_tentang' => 'Ketertiban Umum di Wilayah Kabupaten Buleleng',
                'berkas_pdf' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'kode_regulasi' => 'REG-2018-002',
                'jenis_peraturan' => 'PERDA',
                'nomor_peraturan' => '2',
                'tahun_peraturan' => 2018,
                'judul_tentang' => 'Kawasan Tanpa Rokok (KTR) di Fasilitas Publik Kabupaten Buleleng',
                'berkas_pdf' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'kode_regulasi' => 'REG-2020-039',
                'jenis_peraturan' => 'PERBUP',
                'nomor_peraturan' => '39',
                'tahun_peraturan' => 2020,
                'judul_tentang' => 'Pengelolaan Sampah Berbasis Sumber di Desa/Kelurahan Buleleng',
                'berkas_pdf' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($regulasis as $reg) {
            PerdaPerbup::create($reg);
        }

        // 3. Seed Portal SDA (Tabel Profil & Kompetensi Aparatur)
        $personels = [
            [
                'id_personel' => 'SDA-PERS-2026-001',
                'nip_kontrak' => '198012122005011002',
                'nama_lengkap' => 'I Gede Sukadana, S.Sos.',
                'status_kepegawaian' => 'ASN',
                'pangkat_golongan' => 'Penata Tingkat I - III/d',
                'jabatan' => 'Kepala Bidang',
                'penempatan_bidang' => 'SDA',
                'rekam_pelatihan' => 'Diklat Dasar Satpol PP, Diklat PPNS (Penyidik Pegawai Negeri Sipil)',
                'nomor_sertifikat' => 'SRT/SDA/2026/001',
                'status_keaktifan' => 'Aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id_personel' => 'SDA-PERS-2026-002',
                'nip_kontrak' => '199203152018021001',
                'nama_lengkap' => 'Made Arya Wiguna',
                'status_kepegawaian' => 'ASN',
                'pangkat_golongan' => 'Penata Muda - III/a',
                'jabatan' => 'Komandan Regu',
                'penempatan_bidang' => 'Trantib',
                'rekam_pelatihan' => 'Diklat Dasar Satpol PP, Sertifikasi Intelijen Dasar',
                'nomor_sertifikat' => 'SRT/SDA/2026/002',
                'status_keaktifan' => 'Aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id_personel' => 'SDA-PERS-2026-003',
                'nip_kontrak' => 'KTR-2024-0089',
                'nama_lengkap' => 'Ketut Sri Wahyuni',
                'status_kepegawaian' => 'Kontrak',
                'pangkat_golongan' => 'Pengatur Muda - II/a',
                'jabatan' => 'Staf Administrasi',
                'penempatan_bidang' => 'SDA',
                'rekam_pelatihan' => 'Pelatihan Penanggulangan Bencana',
                'nomor_sertifikat' => 'SRT/SDA/2026/003',
                'status_keaktifan' => 'Aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id_personel' => 'SDA-PERS-2026-004',
                'nip_kontrak' => '198807092015031003',
                'nama_lengkap' => 'Putu Eka Saputra',
                'status_kepegawaian' => 'ASN',
                'pangkat_golongan' => 'Penata Muda Tingkat I - III/b',
                'jabatan' => 'Fungsional PPNS',
                'penempatan_bidang' => 'Perada',
                'rekam_pelatihan' => 'Diklat PPNS (Penyidik Pegawai Negeri Sipil)',
                'nomor_sertifikat' => 'SRT/SDA/2026/004',
                'status_keaktifan' => 'Aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($personels as $pers) {
            SdaPersonel::create($pers);
        }
    }
}
